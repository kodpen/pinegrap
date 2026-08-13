<?php
/**
 * get_files_for_picker.php
 * Lightweight JSON endpoint used by the style designer's "Yazılımdan Seç"
 * context menu option. Returns CSS / JS / JSON files from the files table
 * so the assets panel can link to existing server-side files.
 */
include('init.php');
$user = validate_user();

header('Content-Type: application/json; charset=utf-8');

$filter = isset($_GET['filter']) ? strtolower(trim($_GET['filter'])) : '';

if ($filter === 'css') {
    $where_types = "'css'";
} elseif ($filter === 'js') {
    $where_types = "'js','json'";
} else {
    $where_types = "'css','js','json'";
}

$rows = db_items(
    "SELECT name, type FROM files
     WHERE type IN ($where_types) AND name != ''
     ORDER BY name ASC
     LIMIT 1000"
);

$result = array();
foreach ($rows as $row) {
    $result[] = array(
        'name' => $row['name'],
        'type' => $row['type'],
        'url'  => OUTPUT_PATH . $row['name'],
    );
}

echo encode_json($result);
exit();
