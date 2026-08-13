<?php
/**
 * PineGrap - Enterprise Website Platform
 *
 * local_sale.php has been replaced by add_order.php.
 * Local orders now use the standard orders table with type='local'.
 */
include('init.php');
header('Location: ' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/add_order.php');
exit();
?>
