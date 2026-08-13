<?php
/**
 * PineGrap - Enterprise Website Platform
 *
 * Performance monitor report — backend admin view of perf_log table populated
 * by the shutdown handler in functions.php (perf_monitor_shutdown).
 *
 * Designer-tier users have no business with server-level diagnostics, so this
 * page is restricted to administrators (USER_ROLE = 0) only.
 *
 * @author      Erdal Güral (Kodpen)
 * @link        https://kodpen.com
 * @copyright   2016–2025 Kodpen
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 */

include('init.php');
$user = validate_user();
validate_area_access($user, 'administrator');

include_once('liveform.class.php');
$liveform = new liveform('view_performance_log');

// ---- Reset (clear all log data) -------------------------------------------
// Admin-only TRUNCATE on the perf_log table. Goes through the standard
// CSRF-token + POST pattern so a stray GET URL can't wipe diagnostics.
// We TRUNCATE rather than DELETE because the table has no foreign-key
// dependencies and TRUNCATE resets AUTO_INCREMENT in one step.
if (isset($_POST['submit_clear'])) {
    validate_token_field();
    if (mysqli_num_rows(mysqli_query(db::$con, "SHOW TABLES LIKE 'perf_log'")) > 0) {
        mysqli_query(db::$con, "TRUNCATE perf_log") or output_error('Query failed.');
        log_activity(lang('cleared all performance log data'), $_SESSION['sessionusername']);
        $liveform->add_notice(lang('Performance log data has been cleared.'));
    }
    // Preserve the active filters so the user lands back on the same view
    // they were looking at, just empty. `go()` prepends URL_SCHEME +
    // HOSTNAME — we still need to give it the absolute path so the
    // resulting URL is `https://host/<software-dir>/...` (matches the
    // pattern every other admin redirect uses, e.g. add_gift_card.php).
    $_redirect_qs = array();
    foreach (array('range','area','search','slow_sort','slow_dir','mem_sort','mem_dir') as $_qk) {
        if (isset($_POST[$_qk]) && $_POST[$_qk] !== '') $_redirect_qs[$_qk] = $_POST[$_qk];
    }
    go(PATH . SOFTWARE_DIRECTORY . '/view_performance_log.php' . ($_redirect_qs ? '?' . http_build_query($_redirect_qs) : ''));
}

// ---- Filters ---------------------------------------------------------------

$range_options = array(
    '1'  => lang('Last 24 hours'),
    '7'  => lang('Last 7 days'),
    '30' => lang('Last 30 days'),
);
$range = isset($_GET['range']) && isset($range_options[$_GET['range']]) ? $_GET['range'] : '7';
$range_seconds = (int) $range * 86400;
$start_timestamp = time() - $range_seconds;

$area_options = array(
    ''         => lang('All'),
    'backend'  => lang('Backend'),
    'frontend' => lang('Frontend'),
);
$area = isset($_GET['area']) && isset($area_options[$_GET['area']]) ? $_GET['area'] : '';

// Free-text search across request_url + script_name. Trim to drop accidental whitespace,
// cap length to keep query plans sane on a heavily indexed table.
$search = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
if (strlen($search) > 200) {
    $search = substr($search, 0, 200);
}

$where = "WHERE log_timestamp >= " . (int) $start_timestamp;
if ($area !== '') {
    $where .= " AND area = '" . escape($area) . "'";
}
if ($search !== '') {
    // escape_like protects against % and _ being interpreted as wildcards by user input.
    $like = '%' . escape_like($search) . '%';
    $where .= " AND (request_url LIKE '" . escape($like) . "' OR script_name LIKE '" . escape($like) . "')";
}

// Sort whitelists — never interpolate raw GET values into ORDER BY.
// Each entry maps a stable URL key to a SQL expression.
$slow_sort_columns = array(
    'area'    => 'area',
    'label'   => 'label',
    'hits'    => 'hits',
    'avg_ms'  => 'avg_ms',
    'max_ms'  => 'max_ms',
    'avg_kb'  => 'avg_kb',
    'max_kb'  => 'max_kb',
    'avg_cpu' => 'avg_cpu',
);
$mem_sort_columns = array(
    'area'   => 'area',
    'label'  => 'label',
    'hits'   => 'hits',
    'max_kb' => 'max_kb',
    'avg_kb' => 'avg_kb',
    'avg_ms' => 'avg_ms',
);

$slow_sort = (isset($_GET['slow_sort']) && isset($slow_sort_columns[$_GET['slow_sort']])) ? $_GET['slow_sort'] : 'avg_ms';
$slow_dir  = (isset($_GET['slow_dir']) && strtolower($_GET['slow_dir']) === 'asc') ? 'asc' : 'desc';

$mem_sort = (isset($_GET['mem_sort']) && isset($mem_sort_columns[$_GET['mem_sort']])) ? $_GET['mem_sort'] : 'max_kb';
$mem_dir  = (isset($_GET['mem_dir']) && strtolower($_GET['mem_dir']) === 'asc') ? 'asc' : 'desc';

// ---- Detect missing table (migration not yet run) --------------------------

$table_exists = mysqli_num_rows(mysqli_query(db::$con, "SHOW TABLES LIKE 'perf_log'")) > 0;

if (!$table_exists) {
    echo pg_page_shell(array(
        'title' => lang('Performance Log'),
        'extra classes' => 'setting',
        'icon' => 'setting',
        'heading' => lang('Performance Log'),
        'cancel' => array(
            'enable' => 'true',
            'title' => lang('Return to Settings'),
            'url'   => 'settings.php',
        ),
        'auto_main' => false,
    ));
    echo '<main id="content" class="container">
        <div class="alert alert-warning my-4">
            <i class="bi bi-exclamation-triangle me-2"></i>'
            . lang('The perf_log table does not exist yet. Please run the software upgrade to create it.')
        . '</div>
    </main>';
    echo output_footer();
    exit;
}

// ---- Summary KPIs ----------------------------------------------------------

$summary = mysqli_fetch_assoc(mysqli_query(db::$con,
    "SELECT
        COUNT(*) AS total,
        COALESCE(AVG(duration_ms), 0) AS avg_duration,
        COALESCE(MAX(duration_ms), 0) AS max_duration,
        COALESCE(AVG(peak_memory_kb), 0) AS avg_memory,
        COALESCE(MAX(peak_memory_kb), 0) AS max_memory,
        COALESCE(AVG(cpu_user_ms + cpu_system_ms), 0) AS avg_cpu
    FROM perf_log $where"));

// p95 — MySQL has no native percentile, so for small windows we fetch the row
// at the right offset. For very large windows we cap the sample to keep the
// report itself fast (defeats the purpose otherwise).
$total = (int) $summary['total'];
$p95_duration = 0;
if ($total > 0) {
    // A large OFFSET is the wrong tool here: MySQL has to walk every row it
    // skips, so on a table with millions of rows the report page becomes the
    // heaviest query on the server — a performance monitor that hurts
    // performance whenever you look at it.
    //
    // Above the sample threshold the percentile is computed from the slowest
    // slice instead. The 95th percentile lies inside the top 5% by
    // definition, so reading that slice and taking its last value gives the
    // same answer while touching a bounded number of rows.
    $p95_sample_limit = 20000;

    if ($total <= $p95_sample_limit) {
        $offset = (int) max(0, floor($total * 0.95) - 1);
        $row = mysqli_fetch_assoc(mysqli_query(db::$con,
            "SELECT duration_ms FROM perf_log $where ORDER BY duration_ms ASC LIMIT 1 OFFSET $offset"));
        if ($row) {
            $p95_duration = (int) $row['duration_ms'];
        }
    } else {
        $slice = (int) max(1, min($p95_sample_limit, ceil($total * 0.05)));
        $row = mysqli_fetch_assoc(mysqli_query(db::$con,
            "SELECT MIN(duration_ms) AS p95 FROM (
                SELECT duration_ms FROM perf_log $where
                ORDER BY duration_ms DESC LIMIT $slice
             ) AS slowest"));
        if ($row) {
            $p95_duration = (int) $row['p95'];
        }
    }
}

// ---- Top slowest pages (grouped) ------------------------------------------

// Group key: for frontend, request_url is the meaningful identifier (which page);
// for backend, script_name is (every backend script has a single URL anyway).
// Using a single grouping expression keeps this in one query — COALESCE/IF picks
// the right label per row.
$slowest_order = $slow_sort_columns[$slow_sort] . ' ' . strtoupper($slow_dir);
$slowest_query =
    "SELECT
        IF(area = 'frontend',
           IF(request_url = '' OR request_url = '/', '[home]', request_url),
           script_name) AS label,
        area,
        COUNT(*) AS hits,
        AVG(duration_ms) AS avg_ms,
        MAX(duration_ms) AS max_ms,
        AVG(peak_memory_kb) AS avg_kb,
        MAX(peak_memory_kb) AS max_kb,
        AVG(cpu_user_ms + cpu_system_ms) AS avg_cpu
    FROM perf_log
    $where
    GROUP BY label, area
    HAVING hits >= 1
    ORDER BY $slowest_order
    LIMIT 25";

$slowest_result = mysqli_query(db::$con, $slowest_query);

// ---- Top memory-hungry pages -----------------------------------------------

$memory_order = $mem_sort_columns[$mem_sort] . ' ' . strtoupper($mem_dir);
$memory_query =
    "SELECT
        IF(area = 'frontend',
           IF(request_url = '' OR request_url = '/', '[home]', request_url),
           script_name) AS label,
        area,
        COUNT(*) AS hits,
        AVG(peak_memory_kb) AS avg_kb,
        MAX(peak_memory_kb) AS max_kb,
        AVG(duration_ms) AS avg_ms
    FROM perf_log
    $where
    GROUP BY label, area
    ORDER BY $memory_order
    LIMIT 25";

$memory_result = mysqli_query(db::$con, $memory_query);

// ---- Recent slowest individual requests -----------------------------------

$recent_slow_query =
    "SELECT id, request_url, script_name, area, method, http_status,
            duration_ms, peak_memory_kb, cpu_user_ms, cpu_system_ms,
            user_id, log_timestamp
    FROM perf_log
    $where
    ORDER BY duration_ms DESC
    LIMIT 50";

$recent_slow_result = mysqli_query(db::$con, $recent_slow_query);

// ---- Render helpers --------------------------------------------------------

function pmon_format_kb($kb) {
    $kb = (float) $kb;
    if ($kb >= 1024 * 1024) {
        return number_format($kb / 1024 / 1024, 2) . ' GB';
    }
    if ($kb >= 1024) {
        return number_format($kb / 1024, 1) . ' MB';
    }
    return number_format($kb) . ' KB';
}

function pmon_duration_class($ms) {
    if ($ms >= 2000) { return 'text-danger fw-bold'; }
    if ($ms >= 500)  { return 'text-warning fw-bold'; }
    return 'text-success';
}

function pmon_area_badge($area) {
    if ($area === 'backend') {
        return '<span class="badge bg-primary">' . lang('Backend') . '</span>';
    }
    return '<span class="badge bg-success">' . lang('Frontend') . '</span>';
}

// Build a sortable column header. $base_params are the GET params that should be preserved
// across clicks (range, area, search, plus the OTHER table's sort state). The link toggles
// asc/desc when clicking the active column; otherwise defaults to desc (most useful first).
function pmon_sort_header($key, $label, $sort_param, $dir_param, $current_sort, $current_dir, $base_params, $extra_th_classes = '') {
    $is_active = ($key === $current_sort);
    $next_dir = 'desc';
    $arrow = '<i class="bi bi-arrow-down-up text-body-tertiary ms-1 small"></i>';
    if ($is_active) {
        if ($current_dir === 'asc') {
            $next_dir = 'desc';
            $arrow = '<i class="bi bi-caret-up-fill ms-1 small"></i>';
        } else {
            $next_dir = 'asc';
            $arrow = '<i class="bi bi-caret-down-fill ms-1 small"></i>';
        }
    }
    $params = $base_params;
    $params[$sort_param] = $key;
    $params[$dir_param]  = $next_dir;
    $href = 'view_performance_log.php?' . h(http_build_query($params));
    $th_class = trim($extra_th_classes . ($is_active ? ' table-active' : ''));
    return '<th class="' . $th_class . '"><a href="' . $href . '" class="text-decoration-none text-body d-inline-flex align-items-center">' . $label . $arrow . '</a></th>';
}

// ---- Build filter form -----------------------------------------------------

$range_select = '';
foreach ($range_options as $value => $label) {
    $selected = ($value === $range) ? ' selected' : '';
    $range_select .= '<option value="' . h($value) . '"' . $selected . '>' . h($label) . '</option>';
}

$area_select = '';
foreach ($area_options as $value => $label) {
    $selected = ($value === $area) ? ' selected' : '';
    $area_select .= '<option value="' . h($value) . '"' . $selected . '>' . h($label) . '</option>';
}

// ---- Build slowest pages table --------------------------------------------

$slowest_rows = '';
if ($slowest_result && mysqli_num_rows($slowest_result) > 0) {
    while ($r = mysqli_fetch_assoc($slowest_result)) {
        $slowest_rows .= '
            <tr>
                <td>' . pmon_area_badge($r['area']) . '</td>
                <td><code>' . h($r['label']) . '</code></td>
                <td class="text-end">' . number_format((int) $r['hits']) . '</td>
                <td class="text-end ' . pmon_duration_class((int) $r['avg_ms']) . '">' . number_format((int) $r['avg_ms']) . ' ms</td>
                <td class="text-end ' . pmon_duration_class((int) $r['max_ms']) . '">' . number_format((int) $r['max_ms']) . ' ms</td>
                <td class="text-end">' . pmon_format_kb($r['avg_kb']) . '</td>
                <td class="text-end">' . pmon_format_kb($r['max_kb']) . '</td>
                <td class="text-end">' . number_format((int) $r['avg_cpu']) . ' ms</td>
            </tr>';
    }
} else {
    $slowest_rows = '<tr><td colspan="8" class="text-center text-muted py-4">' . lang('No data yet for the selected period.') . '</td></tr>';
}

// ---- Build memory-hungry pages table --------------------------------------

$memory_rows = '';
if ($memory_result && mysqli_num_rows($memory_result) > 0) {
    while ($r = mysqli_fetch_assoc($memory_result)) {
        $memory_rows .= '
            <tr>
                <td>' . pmon_area_badge($r['area']) . '</td>
                <td><code>' . h($r['label']) . '</code></td>
                <td class="text-end">' . number_format((int) $r['hits']) . '</td>
                <td class="text-end fw-bold">' . pmon_format_kb($r['max_kb']) . '</td>
                <td class="text-end">' . pmon_format_kb($r['avg_kb']) . '</td>
                <td class="text-end ' . pmon_duration_class((int) $r['avg_ms']) . '">' . number_format((int) $r['avg_ms']) . ' ms</td>
            </tr>';
    }
} else {
    $memory_rows = '<tr><td colspan="6" class="text-center text-muted py-4">' . lang('No data yet for the selected period.') . '</td></tr>';
}

// ---- Build recent slow requests table -------------------------------------

$recent_rows = '';
if ($recent_slow_result && mysqli_num_rows($recent_slow_result) > 0) {
    while ($r = mysqli_fetch_assoc($recent_slow_result)) {
        $when = date('Y-m-d H:i:s', (int) $r['log_timestamp']);
        $label = ($r['area'] === 'frontend' && $r['request_url'] !== '')
            ? $r['request_url']
            : $r['script_name'];
        $recent_rows .= '
            <tr>
                <td><small class="text-muted">' . h($when) . '</small></td>
                <td>' . pmon_area_badge($r['area']) . '</td>
                <td><code>' . h($label) . '</code></td>
                <td><span class="badge bg-secondary">' . h($r['method']) . '</span></td>
                <td class="text-end"><span class="badge ' . ((int) $r['http_status'] >= 400 ? 'bg-danger' : 'bg-success-subtle text-dark') . '">' . (int) $r['http_status'] . '</span></td>
                <td class="text-end ' . pmon_duration_class((int) $r['duration_ms']) . '">' . number_format((int) $r['duration_ms']) . ' ms</td>
                <td class="text-end">' . pmon_format_kb($r['peak_memory_kb']) . '</td>
                <td class="text-end">' . number_format((int) $r['cpu_user_ms'] + (int) $r['cpu_system_ms']) . ' ms</td>
            </tr>';
    }
} else {
    $recent_rows = '<tr><td colspan="8" class="text-center text-muted py-4">' . lang('No data yet for the selected period.') . '</td></tr>';
}

// ---- Sortable column headers ----------------------------------------------

// Each table preserves the other table's sort state (and the global filters)
// when its own headers are clicked, so users can sort one table without
// disturbing the other.
$base_params_slow = array(
    'range'     => $range,
    'area'      => $area,
    'search'    => $search,
    'mem_sort'  => $mem_sort,
    'mem_dir'   => $mem_dir,
);
$base_params_mem = array(
    'range'     => $range,
    'area'      => $area,
    'search'    => $search,
    'slow_sort' => $slow_sort,
    'slow_dir'  => $slow_dir,
);

$slowest_thead =
    '<tr>'
    . pmon_sort_header('area',    lang('Area'),         'slow_sort', 'slow_dir', $slow_sort, $slow_dir, $base_params_slow)
    . pmon_sort_header('label',   lang('Page'),         'slow_sort', 'slow_dir', $slow_sort, $slow_dir, $base_params_slow)
    . pmon_sort_header('hits',    lang('Hits'),         'slow_sort', 'slow_dir', $slow_sort, $slow_dir, $base_params_slow, 'text-end')
    . pmon_sort_header('avg_ms',  lang('Avg Time'),     'slow_sort', 'slow_dir', $slow_sort, $slow_dir, $base_params_slow, 'text-end')
    . pmon_sort_header('max_ms',  lang('Max Time'),     'slow_sort', 'slow_dir', $slow_sort, $slow_dir, $base_params_slow, 'text-end')
    . pmon_sort_header('avg_kb',  lang('Avg Memory'),   'slow_sort', 'slow_dir', $slow_sort, $slow_dir, $base_params_slow, 'text-end')
    . pmon_sort_header('max_kb',  lang('Peak Memory'),  'slow_sort', 'slow_dir', $slow_sort, $slow_dir, $base_params_slow, 'text-end')
    . pmon_sort_header('avg_cpu', lang('Avg CPU'),      'slow_sort', 'slow_dir', $slow_sort, $slow_dir, $base_params_slow, 'text-end')
    . '</tr>';

$memory_thead =
    '<tr>'
    . pmon_sort_header('area',   lang('Area'),        'mem_sort', 'mem_dir', $mem_sort, $mem_dir, $base_params_mem)
    . pmon_sort_header('label',  lang('Page'),        'mem_sort', 'mem_dir', $mem_sort, $mem_dir, $base_params_mem)
    . pmon_sort_header('hits',   lang('Hits'),        'mem_sort', 'mem_dir', $mem_sort, $mem_dir, $base_params_mem, 'text-end')
    . pmon_sort_header('max_kb', lang('Peak Memory'), 'mem_sort', 'mem_dir', $mem_sort, $mem_dir, $base_params_mem, 'text-end')
    . pmon_sort_header('avg_kb', lang('Avg Memory'),  'mem_sort', 'mem_dir', $mem_sort, $mem_dir, $base_params_mem, 'text-end')
    . pmon_sort_header('avg_ms', lang('Avg Time'),    'mem_sort', 'mem_dir', $mem_sort, $mem_dir, $base_params_mem, 'text-end')
    . '</tr>';

// ---- Output ---------------------------------------------------------------

echo pg_page_shell(array(
    'title' => lang('Performance Log'),
    'extra classes' => 'setting',
    'icon' => 'setting',
    'heading' => lang('Performance Log'),
    'cancel' => array(
        'enable' => 'true',
        'title' => lang('Return to Settings'),
        'url'   => 'settings.php',
    ),
    'auto_main' => false,
));

echo '
<main id="content" class="container">
    <div class="row">
        <div class="col-12">
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '

            <div class="row mb-3 flex-wrap align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start">
                    <h2 class="d-inline-block" data-bs-content="' . h(lang('Per-request runtime metrics recorded after each response is flushed to the client.')) . '" title="' . h(lang('Performance Log')) . '">' . lang('Performance Log') . '</h2>
                    <p class="m-0 small text-muted">' . lang('Per-request runtime metrics recorded after each response is flushed to the client.') . '</p>
                </div>
                <div class="col-12 col-md-6">
                    <form action="view_performance_log.php" method="get" class="row g-2 justify-content-md-end align-items-end">
                        <div class="col-12 col-sm">
                            <label class="form-label small mb-0">' . lang('Search') . '</label>
                            <input type="search" name="search" value="' . h($search) . '" class="form-control form-control-sm" placeholder="' . h(lang('Search by page...')) . '">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0">' . lang('Time Range') . '</label>
                            <select name="range" class="form-select form-select-sm" onchange="this.form.submit()">' . $range_select . '</select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0">' . lang('Area') . '</label>
                            <select name="area" class="form-select form-select-sm" onchange="this.form.submit()">' . $area_select . '</select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#perf-log-clear-modal" title="' . h(lang('Clear all performance log data')) . '"><i class="bi bi-trash"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Clear-all confirm modal: TRUNCATE the perf_log table.
                 Posts back to this page with submit_clear=1 + the standard
                 CSRF token (same pattern other destructive admin actions
                 follow). Active filters are forwarded so the user lands
                 on the same view, just empty. -->
            <div class="modal fade" id="perf-log-clear-modal" tabindex="-1" aria-labelledby="perf-log-clear-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="view_performance_log.php" method="post">
                            ' . get_token_field() . '
                            <input type="hidden" name="submit_clear" value="1">
                            <input type="hidden" name="range"     value="' . h($range)     . '">
                            <input type="hidden" name="area"      value="' . h($area)      . '">
                            <input type="hidden" name="search"    value="' . h($search)    . '">
                            <input type="hidden" name="slow_sort" value="' . h($slow_sort) . '">
                            <input type="hidden" name="slow_dir"  value="' . h($slow_dir)  . '">
                            <input type="hidden" name="mem_sort"  value="' . h($mem_sort)  . '">
                            <input type="hidden" name="mem_dir"   value="' . h($mem_dir)   . '">
                            <div class="modal-header">
                                <h5 class="modal-title" id="perf-log-clear-title"><i class="bi bi-exclamation-triangle text-danger me-2"></i>' . lang('Clear Performance Log') . '</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>' . lang('Are you sure you want to delete ALL performance log records? This action cannot be undone.') . '</p>
                                <p class="small text-muted mb-0">' . lang('New requests will start populating the log again immediately.') . '</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . lang('Cancel') . '</button>
                                <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>' . lang('Clear All Data') . '</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row mb-4 g-3">
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body py-3">
                            <div class="small text-body-secondary">' . lang('Total Requests') . '</div>
                            <div class="h4 mb-0 text-body-emphasis">' . number_format($total) . '</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body py-3">
                            <div class="small text-body-secondary">' . lang('Average Duration') . '</div>
                            <div class="h4 mb-0 text-body-emphasis">' . number_format((int) $summary['avg_duration']) . ' <small>ms</small></div>
                            <div class="small text-body-secondary">p95: ' . number_format($p95_duration) . ' ms · max: ' . number_format((int) $summary['max_duration']) . ' ms</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body py-3">
                            <div class="small text-body-secondary">' . lang('Average Peak Memory') . '</div>
                            <div class="h4 mb-0 text-body-emphasis">' . pmon_format_kb($summary['avg_memory']) . '</div>
                            <div class="small text-body-secondary">' . lang('Peak') . ': ' . pmon_format_kb($summary['max_memory']) . '</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body py-3">
                            <div class="small text-body-secondary">' . lang('Average CPU Time') . '</div>
                            <div class="h4 mb-0 text-body-emphasis">' . number_format((int) $summary['avg_cpu']) . ' <small>ms</small></div>
                            <div class="small text-body-secondary">' . lang('User + system') . '</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-stopwatch me-2"></i>' . lang('Slowest Pages (by average)') . '</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>' . $slowest_thead . '</thead>
                        <tbody>' . $slowest_rows . '</tbody>
                    </table>
                    </div>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-memory me-2"></i>' . lang('Most Memory-Intensive Pages') . '</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>' . $memory_thead . '</thead>
                        <tbody>' . $memory_rows . '</tbody>
                    </table>
                    </div>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>' . lang('Slowest Individual Requests') . '</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>' . lang('Time') . '</th>
                                <th>' . lang('Area') . '</th>
                                <th>' . lang('Page') . '</th>
                                <th>' . lang('Method') . '</th>
                                <th class="text-end">' . lang('Status') . '</th>
                                <th class="text-end">' . lang('Duration') . '</th>
                                <th class="text-end">' . lang('Peak Memory') . '</th>
                                <th class="text-end">' . lang('CPU') . '</th>
                            </tr>
                        </thead>
                        <tbody>' . $recent_rows . '</tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>' . output_footer();
