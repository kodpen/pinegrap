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
$user = validate_user();
validate_area_access($user, 'manager');
validate_token_field();

$cleared = array();

// ── 0. LiteSpeed Server-Level Cache Purge ────────────────────────────────────
// Send header to LiteSpeed web server to purge ALL cached pages immediately
if (!headers_sent()) {
    header('X-LiteSpeed-Purge: *');
    $cleared[] = 'LiteSpeed Cache';
}

// Touching .htaccess forces LiteSpeed to recycle LSPHP processes and reload configuration
$htaccess_file = dirname(__FILE__) . '/.htaccess';
if (file_exists($htaccess_file) && @touch($htaccess_file)) {
    $cleared[] = 'LiteSpeed Worker Reload (.htaccess)';
}

// ── 1. PHP OPcache (bytecode) ────────────────────────────────────────────────
if (function_exists('opcache_reset')) {
    if (@opcache_reset()) {
        $cleared[] = 'OPcache (Reset)';
    }
}

// Force individual file invalidation for strict environments
if (function_exists('opcache_invalidate')) {
    $root_dir = dirname(__FILE__) . '/';
    $invalidated_count = 0;

    try {
        $directory = new RecursiveDirectoryIterator($root_dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator  = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file_info) {
            if ($file_info->isFile() && $file_info->getExtension() === 'php') {
                if (@opcache_invalidate($file_info->getPathname(), true)) {
                    $invalidated_count++;
                }
            }
        }
    } catch (Exception $e) {
        $php_files = glob($root_dir . '*.php');
        if (is_array($php_files)) {
            foreach ($php_files as $f) {
                if (@opcache_invalidate($f, true)) {
                    $invalidated_count++;
                }
            }
        }
    }

    if ($invalidated_count > 0) {
        $cleared[] = 'OPcache Files Cleared (' . $invalidated_count . ')';
    }
}

// ── 2. APC / APCu (application cache) ───────────────────────────────────────
if (function_exists('apc_clear_cache')) {
    @apc_clear_cache('user');
    @apc_clear_cache('opcode');
    $cleared[] = 'APC';
}
if (function_exists('apcu_clear_cache')) {
    @apcu_clear_cache();
    $cleared[] = 'APCu';
}

// ── 3. PHP file-stat cache ───────────────────────────────────────────────────
clearstatcache(true);

// ── 4. Touch backend CSS / JS ────────────────────────────────────────────────
// The backend header includes these as "?v=filemtime(...)" query strings.
// Touching them forces browsers to download a fresh copy on next panel load.
$backend_dir   = dirname(__FILE__) . '/assets/';
$backend_files = array(
    $backend_dir . 'backend.min.css',
    $backend_dir . 'backend.min.js',
);
$backend_touched = 0;
foreach ($backend_files as $f) {
    if (file_exists($f) && @touch($f)) {
        $backend_touched++;
    }
}
if ($backend_touched > 0) {
    $cleared[] = lang('Backend assets') . ' (' . $backend_touched . ')';
}

// ── 5. Touch frontend system CSS / JS ───────────────────────────────────────
// Files like pinegrap.min.css, frontend.min.css, responsive.min.css etc. are
// referenced via smart_cache() which appends ?v=filemtime(...).
// Touching them updates that timestamp so site visitors re-download them.
$root             = dirname(__FILE__) . '/';
$frontend_touched = 0;
$min_css = glob($root . '*.min.css');
if (is_array($min_css)) {
    foreach ($min_css as $f) {
        if (@touch($f)) {
            $frontend_touched++;
        }
    }
}
$min_js = glob($root . '*.min.js');
if (is_array($min_js)) {
    foreach ($min_js as $f) {
        if (@touch($f)) {
            $frontend_touched++;
        }
    }
}
if ($frontend_touched > 0) {
    $cleared[] = lang('Frontend assets') . ' (' . $frontend_touched . ')';
}

// ── 6. Touch user-uploaded CSS / JS (theme files) ───────────────────────────
// Theme stylesheets live in the files directory and are served by get_file.php
// which sends Last-Modified headers. Touching them invalidates browser caches
// that still respect the Last-Modified / If-Modified-Since handshake.
$files_dir     = FILE_DIRECTORY_PATH . '/';
$theme_touched = 0;
$css_files     = glob($files_dir . '*.css');
if (is_array($css_files)) {
    foreach ($css_files as $f) {
        if (@touch($f)) {
            $theme_touched++;
        }
    }
}
$js_files = glob($files_dir . '*.js');
if (is_array($js_files)) {
    foreach ($js_files as $f) {
        if (@touch($f)) {
            $theme_touched++;
        }
    }
}
if ($theme_touched > 0) {
    $cleared[] = lang('Theme files') . ' (' . $theme_touched . ')';
}

// ── 7. Touch user-uploaded images ───────────────────────────────────────────
// Images are also served via get_file.php with Last-Modified headers.
// Touching them causes the server to report a new modification time, so
// browsers that check If-Modified-Since will re-fetch them.
$image_exts     = array('jpg','jpeg','png','gif','webp','svg','bmp','tif','tiff','ico');
$images_touched = 0;
foreach ($image_exts as $ext) {
    $match_lower = glob($files_dir . '*.' . $ext);
    if (is_array($match_lower)) {
        foreach ($match_lower as $f) {
            if (@touch($f)) {
                $images_touched++;
            }
        }
    }
    $match_upper = glob($files_dir . '*.' . strtoupper($ext));
    if (is_array($match_upper)) {
        foreach ($match_upper as $f) {
            if (@touch($f)) {
                $images_touched++;
            }
        }
    }
}
if ($images_touched > 0) {
    $cleared[] = lang('Images') . ' (' . $images_touched . ')';
}

$message = implode(', ', $cleared);

log_activity(lang(array('string' => 'Cache purged ({var:1}).', 'vars' => array($message))), $_SESSION['sessionusername']);

include_once('liveform.class.php');
$liveform = new liveform('settings');
$liveform->add_notice(lang(array('string' => 'Cache cleared: {var:1}', 'vars' => array($message))));
header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/settings.php');
exit();