<?php
/**
 * PineGrap - Enterprise Website Platform
 *
 * Barcode menu page has been replaced.
 * Inventory operations (increase/decrease) are now tabs in view_products.php.
 * Local sale orders are now created via add_order.php.
 */
include('init.php');
header('Location: ' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_products.php');
exit();
?>
