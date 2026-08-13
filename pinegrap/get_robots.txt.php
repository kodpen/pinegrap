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
// do not translate Sitemap text. its functional string.
$content = 'Sitemap: ' . URL_SCHEME . HOSTNAME_SETTING . PATH . 'sitemap.xml';

// get additional robots.txt content
$query = "SELECT additional_robots_content FROM config";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$additional_robots_content = $row['additional_robots_content'];

// if there is additional robots content then add it to the content
if ($additional_robots_content != '') {
    $content .=
        "\r\n" .
        "\r\n" .
        $additional_robots_content;
}

header('Content-type: text/plain');
print $content;
?>