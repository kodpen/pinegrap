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

/*
  config.php example
  ---------------------------------
  Normally you do not need to edit this file.
  If [software_directory]/data/config.php is deleted,
  you can copy this file, fill in the values,
  and upload it as data/config.php.
*/

/* -----------------------------
   Database Settings
------------------------------ */
define('DB_HOST', 'localhost');     // Example: 'localhost' or '127.0.0.1'
define('DB_USERNAME', 'pinegrap');  // Example: 'root'
define('DB_PASSWORD', 'secret');    // Example: 'mypassword'
define('DB_DATABASE', 'cmsdb');     // Example: 'pinegrap_cms'

// Legacy MySQL connection (for PHP < 7)
// Only enable if old custom code requires mysql_* functions
define('DB_LEGACY', false);

/* -----------------------------
   General Settings
------------------------------ */
define('CDN', true);                  // Use Google CDN for assets
define('ENCRYPTION_KEY', 'my-secret'); // Auto-generated during install, do not change
define('DYNAMIC_REGIONS', true);       // Allow dynamic PHP regions in pages
define('PHP_REGIONS', true);           // Allow PHP code in Page Designer regions

/* -----------------------------
   SMTP Mail Settings
------------------------------ */
// System emails
define('SYSTEM_SMTP_HOSTNAME', 'smtp.example.com');
define('SYSTEM_SMTP_USERNAME', 'noreply@example.com');
define('SYSTEM_SMTP_PASSWORD', 'password123');
define('SYSTEM_SMTP_PORT', '587'); // Default: 587

// Email campaign (requires cron job)
define('EMAIL_CAMPAIGN_JOB', true);
define('EMAIL_CAMPAIGN_JOB_NUMBER_OF_EMAILS', '25'); // Default: 25
define('CAMPAIGN_SMTP_HOSTNAME', 'smtp.example.com');
define('CAMPAIGN_SMTP_USERNAME', 'campaign@example.com');
define('CAMPAIGN_SMTP_PASSWORD', 'password123');
define('CAMPAIGN_SMTP_PORT', '587'); // Default: 587

// DKIM signing (optional, improves deliverability)
define('DKIM_DOMAIN', 'example.com');   // Example: 'pinegrap.com'
define('DKIM_SELECTOR', 'mail');        // Example: 'default'

/* -----------------------------
   Environment / Development
------------------------------ */
define('ENVIRONMENT', 'production'); // 'development' or 'production'
define('EDITION', 'Premium');        // Edition name (shown in backend footer)

/* -----------------------------
   Developer Lock Settings
------------------------------ */
// Lock specific backend pages with a developer PIN.
// The PIN can be any length (e.g. 4, 6, 8 digits or more).
// The PIN must be numeric.
// Users will be redirected to developer_lock.php if they try to access a locked page without the correct PIN.
// Example: '123456'
define('DEVELOPER_PIN', '123456');

// List of backend pages that require PIN unlock
const LOCKED_PAGES = array(
  "barcode_menu.php",
  "welcome.php"
);

/* -----------------------------
   UI / Branding
------------------------------ */
define('LOGO_URL', '/pinegrap/assets/images/logo.png'); // Default logo path
define('CONTROL_PANEL_STYLESHEET_URL', '/pinegrap/assets/css/backend.css'); // Default backend CSS

/* -----------------------------
   Error Reporting
------------------------------ */
// If true (or not defined), Pinegrap suppresses notices and deprecated warnings.
// Set to false to let php.ini control error reporting.
define('SET_ERROR_REPORTING', true);

/* -----------------------------
   Language Settings
------------------------------ */
// Default software language (used if DB not available)
// Example: 'en' or 'tr'
define('DEFAULT_SOFTWARE_LANGUAGE','en');

// Enforcement language (overrides all settings, strict)
// Not recommended unless necessary
define('ENFORCEMENT_SOFTWARE_LANGUAGE','');

/* -----------------------------
   Maintenance / Updates
------------------------------ */
// Disable software update check (not recommended)
define('SOFTWARE_UPDATE_CHECK', true);

// Enable migration feature
define('MIG', true);

// Enable/disable automatic backups
define('SOFTWARE_AUTO_BACKUP', true);

// Require HTTPS for all requests
define('REQUIRE_SECURE_MODE', false);

// Trust the SSL headers forwarded by a reverse proxy or CDN
// (X-Forwarded-Proto, CF-Visitor, X-Forwarded-SSL, Front-End-Https).
//
// Required when SSL is terminated before the request reaches this server, so
// that the origin leg is plain HTTP - Cloudflare "Flexible" SSL, nginx/HAProxy
// offloading, most load balancers. Without this, Secure Mode cannot tell that
// the visitor is on HTTPS and will refuse or redirect every request.
//
// SECURITY: these headers are sent by the client, so anyone who can reach this
// server directly can forge them and bypass Secure Mode. Only enable it when
// the origin is not publicly reachable - firewall it to the proxy's IP ranges
// (Cloudflare publishes theirs), or use Authenticated Origin Pulls.
define('TRUST_PROXY_SSL_HEADERS', false);

// cloudflare api entegration.
define('CLOUDFLARE_API_TOKEN', '');
define('CLOUDFLARE_ZONE_ID', '');

// ── Performance monitor ─────────────────────────────────────────────────
//
// Writes one row per request to perf_log after the response has been sent.
// The cost is small per request but scales with traffic, so on a busy site
// lower the sample rate rather than turning the monitor off entirely: a 10%
// sample gives the same averages and percentiles for a tenth of the writes.
//
// Note: the "write after the response" trick relies on fastcgi_finish_request(),
// which exists only under PHP-FPM. On mod_php, CGI or IIS the write happens
// before the response completes and the visitor waits for it — sample low.

// Turn the monitor off completely.
define('PERF_MONITOR', true);

// Percentage of requests to record (1-100).
define('PERF_MONITOR_SAMPLE_RATE', 100);

// Only record requests slower than this many milliseconds. 0 records all.
define('PERF_MONITOR_MIN_DURATION_MS', 0);

// Days of history to keep.
define('PERF_MONITOR_RETENTION_DAYS', 30);

// Hard ceiling on stored rows. Retention by age alone cannot bound the table
// on a busy site; this can. Oldest rows are dropped beyond it.
define('PERF_MONITOR_MAX_ROWS', 200000);

?>