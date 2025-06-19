<?php
// Test script to check WordPress admin menu structure
require_once 'wp-config.php';
require_once 'wp-admin/includes/admin.php';

global $menu, $submenu;

// Trigger admin_menu hook
do_action('admin_menu');

echo "Main menu items:\n";
foreach ($menu as $item) {
    if (strpos($item[2], 'yith') !== false || strpos($item[0], 'Point') !== false) {
        echo "Menu: " . $item[0] . " -> " . $item[2] . "\n";
    }
}

echo "\nYITH POS submenus:\n";
if (isset($submenu['yith_pos_panel'])) {
    foreach ($submenu['yith_pos_panel'] as $item) {
        echo "Submenu: " . $item[0] . " -> " . $item[2] . "\n";
    }
} else {
    echo "No yith_pos_panel submenus found\n";
}

echo "\nAll submenu parents:\n";
foreach ($submenu as $parent => $items) {
    if (strpos($parent, 'yith') !== false) {
        echo "Parent: " . $parent . "\n";
    }
}
?>
