<?php
/**
 * PineGrap - Enterprise Website Platform
 *
 * Originally developed as LiveSite by Camelback Web Architects.
 * Since 2017, maintained and evolved by Erdal Güral (Kodpen) under the name PineGrap.
 * The final LiveSite update (2019) has been integrated into PineGrap.
 * LiveSite remains available as a separate downloadable legacy version.
 *
 * @author      Camelback Web Architects
 *              Erdal Güral (Kodpen)
 * @link        https://livesite.com
 *              https://kodpen.com
 * @copyright   2001–2019 Camelback Consulting, Inc.
 *              2016–2025 Kodpen
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 */

include('init.php');
ini_set('memory_limit', '512M');
$user = validate_user();
validate_area_access($user, 'manager');

include_once('liveform.class.php');
$liveform = new liveform('view_log');

// Path to error log file (can be extended later to include other locations)
$error_log_path = 'error_log';
$base_dir = dirname(__FILE__);
$candidates = array(
    'software_directory' => $base_dir . DIRECTORY_SEPARATOR . $error_log_path,
    'main_directory'         => $base_dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $error_log_path,
    'installation_directory'       => $base_dir . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . $error_log_path,
);

if (!$_POST) {

    // Get number of all timestamps.
    $query = "SELECT COUNT(log_id) "
        ."FROM log ";
    $result = mysqli_query(db::$con, $query) or output_error("Query failed.");
    $row = mysqli_fetch_row($result);
    $all_logs = $row[0];

    // get oldest timestamp
    $query = "SELECT MIN(log_timestamp) "
        ."FROM log ";
    $result = mysqli_query(db::$con, $query) or output_error("Query failed.");
    $row = mysqli_fetch_row($result);
    $oldest_timestamp = $row[0];


    // if advanced filters value was passed in the query string
    if (isset($_REQUEST['advanced_filters']) == true) {
        // if advanced filters should be turned on
        if ($_REQUEST['advanced_filters'] == 'true') {
            $_SESSION['software']['settings']['view_log']['advanced_filters'] = true;

        // else advanced filters should be turned off
        } else {
            $_SESSION['software']['settings']['view_log']['advanced_filters'] = false;
        }
    }

    $output_control_buttons = '';
    if ((MASS_DELETION == true) || (defined('USER_ROLE') && (USER_ROLE < 1))) {
        $checked = (!empty($_SESSION['software']['settings']['view_log']['error_log'])) ? 'checked' : '';
    
        $delete_logs_button = '';
        if (MASS_DELETION == true && $all_logs > 0) {
            $delete_logs_button = '
                <button type="submit" name="delete_all_site_logs" value="Delete Site Logs" class="btn btn-link link-danger py-0 mb-2 bi bi-trash bi-me-2" onclick="event.preventDefault(); var b=this; pgConfirm({title:\'' . lang('Delete All Site Logs') . '\', message:\'' . lang('WARNING: All site logs will be permanently deleted. View All button will allow you to see which logs will be deleted before you actually delete them. If you would like to continue with the deletion, please click OK. Otherwise, please click Cancel.') . '\', confirmText:\'' . lang('Delete') . '\', cancelText:\'' . lang('Cancel') . '\', variant:\'danger\'}).then(function(ok){if(ok){b.disabled=true;b.form.appendChild(Object.assign(document.createElement(\'input\'),{type:\'hidden\',name:\'delete_all_site_logs\',value:\'Delete Site Logs\'}));b.form.submit();}}); return false;">' . lang('Delete All Site Logs') . '</button>';
        }
    
        $delete_error_logs_button = '';
        $error_log_controls = '';
        if (defined('USER_ROLE') && (USER_ROLE < 1)) {

            if(
                $_SESSION['software']['settings']['view_log']['error_log'] 
                && (
                    file_exists($candidates['software_directory'])
                    || file_exists($candidates['main_directory'])
                    || file_exists($candidates['installation_directory'])
                )
            ) {
                $delete_error_logs_button = '<button type="submit" name="delete_all_error_logs" value="Delete Error Logs"  value="1" class="btn btn-link link-danger py-0 mb-2 bi bi-trash bi-me-2" onclick="event.preventDefault(); var b=this; pgConfirm({title:\'' . lang('Delete All Error Logs') . '\', message:\'' . lang('WARNING: error_log files will be permanently deleted around Software. If you would like to continue with the deletion, please click OK. Otherwise, please click Cancel.') . '\', confirmText:\'' . lang('Delete') . '\', cancelText:\'' . lang('Cancel') . '\', variant:\'danger\'}).then(function(ok){if(ok){b.disabled=true;b.form.appendChild(Object.assign(document.createElement(\'input\'),{type:\'hidden\',name:\'delete_all_error_logs\',value:\'Delete Error Logs\'}));b.form.submit();}}); return false;">' . lang('Delete All Error Logs') . '</button>';
            }
            
        
            $error_log_controls = '
                <div class="form-check form-switch me-3 align-middle">
                    <input class="form-check-input" type="checkbox" name="error_log_toggle" id="error_log_toggle" ' . $checked . '>
                    <label class="form-check-label" for="error_log_toggle">' . lang('Show Error Logs') . '</label>
                </div>
                <script>
                  document.getElementById("error_log_toggle").addEventListener("change", function() {
                    document.getElementById("view_log_controls").submit();
                  });
                </script>';
        }
    
        $output_control_buttons = '
            <form action="view_log.php" method="post" name="view_log_controls" id="view_log_controls" class="d-inline-block disable_shortcut">
                ' . get_token_field() . '
                ' . $delete_logs_button . '
                ' . $delete_error_logs_button . '
                ' . $error_log_controls . '
            </form>';
    }

    // if view all was selected
    if (isset($_GET['view']) && ($_GET['view'] == 'all')) {
        $start_month = date('m', $oldest_timestamp);
        $start_day = date('d', $oldest_timestamp);
        $start_year = date('Y', $oldest_timestamp);
        $stop_month = date('m');
        $stop_day = date('d');
        $stop_year = date('Y');

    // if date range has not be completed yet
    } elseif (!isset($_REQUEST['start_month']) || !$_REQUEST['start_month']) {
        // assign default values to start and stop dates (default to ~31 days ago)
        $start_month = date('m', time() - 2678400);
        $start_day = date('d', time() - 2678400);
        $start_year = date('Y', time() - 2678400);
        $stop_month = date('m');
        $stop_day = date('d');
        $stop_year = date('Y');

    } else {
        $start_month = $_REQUEST['start_month'];
        $start_day = $_REQUEST['start_day'];
        $start_year = $_REQUEST['start_year'];
        $stop_month = $_REQUEST['stop_month'];
        $stop_day = $_REQUEST['stop_day'];
        $stop_year = $_REQUEST['stop_year'];

    }
    // get minimum year from oldest timestamp
    $oldest_year = date('Y', $oldest_timestamp);
    $current_year = date('Y');
    // create html for year options
    $year_options = '';
    for ($i = $oldest_year; $i <= $current_year; $i++) {
        $year_options .= '<option value="'.$i.'">'.$i.'</option>';
    }
    // get timestamps for start and stop dates
    $start_timestamp = mktime (0, 0, 0, $start_month, $start_day, $start_year);
    $stop_timestamp = mktime (23, 59, 59, $stop_month, $stop_day, $stop_year);

    // get query data for URL
    $keys_and_values = h("&start_month=$start_month&start_day=$start_day&start_year=$start_year&stop_month=$stop_month&stop_day=$stop_day&stop_year=$stop_year");

    switch((isset($_GET['sort']) ? $_GET['sort'] : '')) {
        case lang('Time'):
        $sort_column = 'log_timestamp';
        break;
        case lang('User'):
        $sort_column = 'log_user';
        break;
        case lang('Description'):
        $sort_column = 'log_description';
        break;
        case lang('IP Address'):
        $sort_column = 'log_ip';
        break;
        default:
        $sort_column = 'log_timestamp';
    }
    if(isset($_GET['sort'])) {
        $asc_desc = isset($_GET['order']) ? $_GET['order'] : '';
    } else {
        $asc_desc = 'DESC';
    }
    if((isset($_GET['sort']) && ($_GET['sort'] == 'log_timestamp')) && (!isset($_GET['order']) || !$_GET['order'])) {
        $asc_desc = 'ASC';
    }

    // Determine whether to include error_log entries (only for admins and if enabled in settings)
    $include_error_logs = (defined('USER_ROLE') && (USER_ROLE < 1) && !empty($_SESSION['software']['settings']['view_log']['error_log']));

    $output_rows = '';
    $number_of_results = 0;

    if (!$include_error_logs) {
        // Fast path: only DB logs are needed — stream results directly without building intermediate arrays
        $query = "SELECT log_id, log_description, log_ip, log_user, log_timestamp "
            ."FROM log "
            ."WHERE (log_timestamp >= $start_timestamp) AND (log_timestamp <= $stop_timestamp) "
            ."ORDER BY $sort_column $asc_desc, log_id $asc_desc ";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');

        while ($row = mysqli_fetch_assoc($result)) {
            $log_id = $row['log_id'];
            $log_description = $row['log_description'];
            $log_ip = isset($row['log_ip']) ? $row['log_ip'] : '';
            $log_user = $row['log_user'] === '' ? 'UNKNOWN' : $row['log_user'];
            $log_timestamp = $row['log_timestamp'];
            $log_location = lang('Database');
            $number_of_results++;

            $output_rows .= '
            <tr id="' . h($log_id) . '" class="unselectable ">
                <td>'. get_relative_time(array('timestamp' => $log_timestamp)). '</td>
                <td><span title="' . h($log_location) . '" class="badge bg-success me-1">' . lang('Site Log') . '</span></td>
                <td>' . h($log_user) . '</td>
                <td>' . nl2br(convert_text_to_html($log_description)) . '</td>
                <td>' . h($log_ip) . '</td>
            </tr>';
        }
    } else {
        // Full path: collect DB logs into array, gather error_log entries, merge and sort
        $db_logs = array();
        $query = "SELECT log_id, log_description, log_ip, log_user, log_timestamp "
            ."FROM log "
            ."WHERE (log_timestamp >= $start_timestamp) AND (log_timestamp <= $stop_timestamp) ";
        // keep SQL ordering as a stable base; final ordering will be enforced after merging
        $query .= "ORDER BY $sort_column $asc_desc, log_id $asc_desc ";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['log_user'] == '') {
                $row['log_user'] = 'UNKNOWN';
            }
            $row['log_type'] = 'Site Log';
            $row['log_location'] = lang('Database');
            
            $db_logs[] = $row;
        }

        // Read error_log files from possible locations
        $error_logs = array();


        foreach ($candidates as $locationmark => $candidate_path) {
            $candidate_path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $candidate_path);
            $candidate_real = @realpath($candidate_path) ?: $candidate_path;

            if (!file_exists($candidate_real) || !is_readable($candidate_real) || !is_file($candidate_real)) {
            continue;
            }

            $lines = @file($candidate_real, FILE_IGNORE_NEW_LINES);
            if ($lines === false) {
            continue;
            }

            $grouped = array();
            $current_index = -1;

            foreach ($lines as $raw_line) {
            $line = rtrim($raw_line);
            if ($line === '') {
                if ($current_index >= 0) {
                $grouped[$current_index]['lines'][] = $line;
                }
                continue;
            }

            $is_new_entry = false;
            $date_candidate = null;

            if (preg_match('/^\[?([0-9]{1,2}-[A-Za-z]{3}-[0-9]{4}\s+[0-9:]+)/', $line, $m)) {
                $is_new_entry = true;
                $date_candidate = $m[1];
            } elseif (preg_match('/^\[?([0-9]{4}-[0-9]{2}-[0-9]{2}\s+[0-9:]+)/', $line, $m)) {
                $is_new_entry = true;
                $date_candidate = $m[1];
            } elseif (preg_match('/^\[?([0-9]{4}\/[0-9]{2}\/[0-9]{2}\s+[0-9:]+)/', $line, $m)) {
                $is_new_entry = true;
                $date_candidate = str_replace('/', '-', $m[1]);
            }

            if ($is_new_entry) {
                $grouped[] = array(
                'date_candidate' => $date_candidate,
                'lines' => array($line),
                );
                $current_index++;
            } else {
                if ($current_index >= 0) {
                $grouped[$current_index]['lines'][] = $line;
                } else {
                $grouped[] = array(
                    'date_candidate' => null,
                    'lines' => array($line),
                );
                $current_index++;
                }
            }
            }

            $file_mtime = @filemtime($candidate_real) ?: time();
            $local_index = 0;
            foreach ($grouped as $g) {
            $timestamp = null;
            if (!empty($g['date_candidate'])) {
                $ts = @strtotime($g['date_candidate']);
                if ($ts !== false) {
                $timestamp = $ts;
                }
            }
            if ($timestamp === null) {
                $timestamp = $file_mtime;
            }
            $locationlabel = $locationmark;
            switch ($locationmark) {
                case 'software_directory':
                $locationlabel = lang('Software Directory');
                break;
                case 'main_directory':
                $locationlabel = lang('Main Directory');
                break;
                case 'installation_directory':
                $locationlabel = lang('Installation Directory');
                break;
            }
            if (($timestamp >= $start_timestamp) && ($timestamp <= $stop_timestamp)) {
                $error_logs[] = array(
                'log_id' => 'error_' . $locationmark . '_' . $local_index,
                'log_type' => 'Error Log',
                'log_description' => implode("\n", $g['lines']),
                'log_ip' => '',
                'log_user' => '[' . lang('SYSTEM') . ']',
                'log_location' => h($locationlabel),
                'log_timestamp' => $timestamp
                );
                $local_index++;
            }
            }
        }

        // Merge and sort
        $all_entries = array_merge($db_logs, $error_logs);

        if (strtoupper($asc_desc) === 'ASC') {
            usort($all_entries, function($a, $b) {
            if ($a['log_timestamp'] == $b['log_timestamp']) {
                return strcmp($a['log_id'], $b['log_id']);
            }
            return ($a['log_timestamp'] < $b['log_timestamp']) ? -1 : 1;
            });
        } else {
            usort($all_entries, function($a, $b) {
            if ($a['log_timestamp'] == $b['log_timestamp']) {
                return strcmp($b['log_id'], $a['log_id']);
            }
            return ($a['log_timestamp'] > $b['log_timestamp']) ? -1 : 1;
            });
        }

        // Build rows
        foreach ($all_entries as $row) {
            $log_id = $row['log_id'];
            $log_description = $row['log_description'];
            $log_ip = isset($row['log_ip']) ? $row['log_ip'] : '';
            $log_user = isset($row['log_user']) ? $row['log_user'] : '';
            if ($log_user == '') {
            $log_user = 'UNKNOWN';
            }
            $log_location = isset($row['log_location']) ? $row['log_location'] : '';
            $log_type = isset($row['log_type']) ? $row['log_type'] : 'Site Log';


            $output_log_type = '';
            if($log_type == 'Site Log') {
            $output_log_type = '<span title="' . $log_location . '" class="badge bg-success me-1">' . h(lang($log_type)) . '</span>';
            } else {
            // Determine badge color based on message severity
            // warning -> bg-warning, fatal/error/uncaught/exception -> bg-danger (default for non-warning)
            $badge_class = 'bg-danger';
            if (preg_match('/\bwarning\b/i', $log_description)) {
                $badge_class = 'bg-warning';
            } elseif (preg_match('/\b(fatal|fatal error|uncaught|uncaught exception|exception|error)\b/i', $log_description)) {
                $badge_class = 'bg-danger';
            }
            $output_log_type = '<span title="' . $log_location . '" class="badge ' . $badge_class . ' me-1">' . h(lang($log_type)) . '</span>';
            }


            $log_timestamp = $row['log_timestamp'];

            $number_of_results++;

            $output_rows .= '
            <tr id="' . h($log_id) . '" class="unselectable ">
                <td>'. get_relative_time(array('timestamp' => $log_timestamp)). '</td>
                <td>' . $output_log_type  . '</td>
                <td>' . h($log_user) . '</td>
                <td>' . nl2br(convert_text_to_html($log_description)) . '</td>
                <td>' . h($log_ip) . '</td>
            </tr>
            ';
        }
    }


    // if the advanced filters are off
    if (empty($_SESSION['software']['settings']['view_log']['advanced_filters'])) {
        $output_advanced_filters_value = 'true';
        $output_advanced_filters_label = lang('Add Advanced Filters');
        $output_advanced_filters = '';
        $advanced_filters_icon = 'filter_list';
        $output_advanced_filters_class = 'btn-primary';
        $output_advanced_filters = '';

    // else the advanced filters are on
    } else {
        $output_advanced_filters_value = 'false';
        $output_advanced_filters_label = lang('Remove Advanced Filters');
        $advanced_filters_icon = 'filter_list_off';
        $output_advanced_filters_class = 'btn-danger';

        $output_advanced_filters = '
        <div class="advanced_filters advanced-filter-bar position-fixed-md" id="advanced_filters">
            <div class="p-2 border justify-content-between d-flex flex-wrap header">
                <p class="m-0"><span class="material-icons pe-1">filter_list</span>' . lang('Filters') . '</p>
                <a class="btn btn-close" title="' . $output_advanced_filters_label . '" href="view_log.php?advanced_filters=' . $output_advanced_filters_value . '"></a>
            </div>
            <form class="advanced-filter-body p-2 pt-0 disable_shortcut" id="search_advanced" action="view_log.php" method="get" name="form">
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-success fw-bold mt-4 mb-2">' . lang('Date Range') . '</h5>
                    </div>
                    <div class="col-12">
                        <label class="form-label">' . lang('From') . '</label>
                        <select class="form-select my-1" name="start_month">' . select_month($_SESSION['software']['settings']['view_log']['start_month']) . '</select>
                        <div class="input-group input-group-sm">
                            <select class="form-select my-1" name="start_day">' . select_day($_SESSION['software']['settings']['view_log']['start_day']) . '</select>
                            <select class="form-select my-1" name="start_year">' . $year_options . '</select>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">' . lang('To') . '</label>
                        <select class="form-select my-1" name="stop_month">' . select_month($_SESSION['software']['settings']['view_log']['stop_month']) . '</select>
                        <div class="input-group input-group-sm">
                            <select class="form-select my-1" name="stop_day">' . select_day($_SESSION['software']['settings']['view_log']['stop_day']) . '</select>
                            <select class="form-select my-1" name="stop_year">' . $year_options . '</select>
                        </div>
                    </div>
                    <div class="col-12 text-center position-sticky my-2" style="bottom:.5rem;">
                        <button type="submit" name="submit_data" value="Update" data-loading-content="' . lang('Updating') . '" class="btn btn-primary my-1">
                            <i class="material-icons me-2">sync</i>' . lang('Update') . '
                        </button>
                    </div>
                </div>
        
                <!-- Prevent undefined index warnings by checking if keys exist -->
                <input type="hidden" name="sort" value="' . (isset($_REQUEST['sort']) ? h($_REQUEST['sort']) : '') . '">
                <input type="hidden" name="order" value="' . (isset($_REQUEST['order']) ? h($_REQUEST['order']) : '') . '">
        
                <script>
                    // Set initial values for date selectors using escaped JS-safe values
                    document.form.start_month.value = "' . escape_javascript($start_month) . '";
                    document.form.start_day.value = "' . escape_javascript($start_day) . '";
                    document.form.start_year.value = "' . escape_javascript($start_year) . '";
                    document.form.stop_month.value = "' . escape_javascript($stop_month) . '";
                    document.form.stop_day.value = "' . escape_javascript($stop_day) . '";
                    document.form.stop_year.value = "' . escape_javascript($stop_year) . '";
                </script>
            </form>
        </div>';

    }



    echo
    
    pg_page_shell(
        array(
            'title'=> lang('Site Log'),
            'extra classes'=>'setting',
            'icon'=>'setting',
            'heading'=> lang('Site Log'),
            'cancel'=>array(
                'enable'=>'true',
                'title'=>lang('Return to Settings'),
                'url'=>'settings.php'
            ),
            'breadcrumb' => array(
                array('label' => lang('Settings'), 'url' => 'settings.php'),
                array('label' => lang('Site Log')),
            ),
            'pre_main_html' => $output_advanced_filters,
        )
    ) . '
        <div class="row">
            <div class="col-12">
                ' . $liveform->output_errors() . '
                ' . $liveform->get_warnings() . '
                ' . $liveform->output_notices() . '
                <div class="row mb-2  flex-wrap">
                    <div class="col-12 col-sm-12 col-md-6 col-xl-9 text-center text-md-start">
                        <h2 class="d-inline-block " data-bs-content="' . lang('Audit all website events and changes by any site visitor or user.') . '" title="' . lang('Site Log') . '">' . lang('Site Log') . '</h2>
                        <nav id="button_bar" class="navigation " aria-label="Button Bar">
                            ' . $output_control_buttons . '
                        </nav>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-xl-3 ">
                        <div class="row justify-content-center justify-content-md-end">
                            <form action="view_log.php" method="get" name="form" class="search_form col-auto">
                                <div class="input-group input-group-sm">   
                                    <a class="btn btn-sm  my-1 ' . $output_advanced_filters_class . '" data-loading-content=" " title="' . $output_advanced_filters_label . '" href="view_log.php?advanced_filters=' . $output_advanced_filters_value . '" ><i class="material-icons">'. $advanced_filters_icon . '</i></a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card my-4">
                    <div class="card-body p-0 position-relative">
                        <table class="chart table-hover table " style="width:100%;display:none">
                            <thead>
                                <tr>
                                    <th>' .asc_or_desc(lang('Time'),'view_log', $keys_and_values). '</th>
                                    <th>' .asc_or_desc(lang('Type'),'view_log', $keys_and_values). '</th>
                                    <th>' .asc_or_desc(lang('User'),'view_log', $keys_and_values). '</th>
                                    <th>' .asc_or_desc(lang('Description'),'view_log', $keys_and_values). '</th>
                                    <th>' .asc_or_desc(lang('IP Address'),'view_log', $keys_and_values). '</th>
                                </tr>
                            </thead>
                            ' . $output_rows . '
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>' . output_footer();

     $liveform->remove_form('settings');
} else {

    
    validate_token_field();


    // save advanced filter settings
    if (isset($_POST['start_month'])) {
        $_SESSION['software']['settings']['view_log']['start_month'] = $_POST['start_month'];
    }
    if (isset($_POST['start_day'])) {
        $_SESSION['software']['settings']['view_log']['start_day'] = $_POST['start_day'];
    }
    if (isset($_POST['start_year'])) {
        $_SESSION['software']['settings']['view_log']['start_year'] = $_POST['start_year'];
    }
    if (isset($_POST['stop_month'])) {
        $_SESSION['software']['settings']['view_log']['stop_month'] = $_POST['stop_month'];
    }
    if (isset($_POST['stop_day'])) {
        $_SESSION['software']['settings']['view_log']['stop_day'] = $_POST['stop_day'];
    }
    if (isset($_POST['stop_year'])) {
        $_SESSION['software']['settings']['view_log']['stop_year'] = $_POST['stop_year'];
    }

    // save error log setting
    $_SESSION['software']['settings']['view_log']['error_log'] = isset($_POST['error_log_toggle']);

    // Handle deletion actions (POST)
    if (isset($_POST['delete_all_site_logs']) && (MASS_DELETION === true)) {
    
        // Count logs first
        $query = "SELECT COUNT(log_id) FROM log";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $number_of_logs = (int)$row[0];
    
        if ($number_of_logs > 0) {
            // Delete all logs in a single query
            $query = "DELETE FROM log";
            mysqli_query(db::$con, $query) or output_error('Query failed.');
        
            $liveform->add_notice(
                lang(array(
                    'string' => '{var:1} site log(s) were deleted.',
                    'vars'   => array(number_format($number_of_logs))
                ))
            );
        } else {
            $liveform->mark_error(lang('No site log were deleted'));
        }
    }
    
    // Delete error_log files (allowed regardless of MASS_DELETION)
    if (isset($_POST['delete_all_error_logs'])) {
    
        $deleted = 0;
    
        foreach ($candidates as $locationmark => $candidate_path) {
            $candidate_path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $candidate_path);
            $candidate_real = @realpath($candidate_path) ?: $candidate_path;
        
            if (!file_exists($candidate_real) || !is_file($candidate_real)) {
                continue;
            }
        
            // Try to unlink; if not writable, attempt to chmod then unlink
            if (is_writable($candidate_real)) {
                if (@unlink($candidate_real)) {
                    $deleted++;
                }
            } else {
                @chmod($candidate_real, 0666);
                if (@unlink($candidate_real)) {
                    $deleted++;
                }
            }
        }
    
        if ($deleted > 0) {
            $liveform->add_notice(lang(array('string'=>'{var:1} error_log file(s) deleted.','vars'=>array($deleted))));
        } else {
            $liveform->mark_error(lang('No error_log files were deleted.'));
        }
    }
    
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_log.php');
    exit;
}