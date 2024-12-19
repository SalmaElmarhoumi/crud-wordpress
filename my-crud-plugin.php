<?php
/**
 * Plugin Name: My CRUD Plugin
 * Description: A simple CRUD plugin for managing data.
 * Version: 1.0
 * Author: Salma El-Marhoumi
 * License: GPL2
 */

// Prevent direct access
defined('ABSPATH') or die('No script kiddies please!');

// Create database table on activation
function my_crud_plugin_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud_items';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        description text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'my_crud_plugin_activate');

// Add admin menu
function my_crud_plugin_menu() {
    add_menu_page(
        'CRUD Plugin',
        'CRUD Plugin',
        'manage_options',
        'my-crud-plugin',
        'plugin_page_html',
        'dashicons-list-view',
        6
    );
}
add_action('admin_menu', 'my_crud_plugin_menu');

// Enqueue styles
function my_crud_enqueue_styles() {
    wp_enqueue_style('my-crud-plugin-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', [], time());
}
add_action('admin_enqueue_scripts', 'my_crud_enqueue_styles');

// Handle form submissions
function handle_form_submission() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud_items';

    if (isset($_POST['add_item'])) {
        $name = sanitize_text_field($_POST['item_name']);
        $description = sanitize_textarea_field($_POST['item_description']);
        $wpdb->insert($table_name, ['name' => $name, 'description' => $description]);
        wp_redirect(admin_url('admin.php?page=my-crud-plugin'));
        exit;
    }

    if (isset($_GET['delete_item_id'])) {
        $item_id = intval($_GET['delete_item_id']);
        $wpdb->delete($table_name, ['id' => $item_id]);
        wp_redirect(admin_url('admin.php?page=my-crud-plugin'));
        exit;
    }
}
add_action('admin_init', 'handle_form_submission');

// Render admin page
function plugin_page_html() {
    ?>
    <div class="wrap">
        <h1>Manage Items</h1>
        <form method="post">
            <label for="item_name">Item Name:</label>
            <input type="text" id="item_name" name="item_name" placeholder="Enter item name" required>
            <label for="item_description">Description:</label>
            <textarea id="item_description" name="item_description" placeholder="Enter item description" required></textarea>
            <input type="submit" name="add_item" value="Add Item">
        </form>
        <h2>Items List</h2>
        <?php display_items(); ?>
    </div>
    <?php
}

// Display items in a table with sequential row numbers
function display_items() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud_items';
    $items = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($items)) {
        echo '<p>No items found.</p>';
        return;
    }

    echo '<table class="widefat">';
    echo '<thead><tr><th>#</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    $row_number = 1; // Initialize row number
    foreach ($items as $item) {
        $delete_url = admin_url("admin.php?page=my-crud-plugin&delete_item_id={$item->id}");
        echo "<tr>
                <td>{$row_number}</td>
                <td>{$item->name}</td>
                <td>{$item->description}</td>
                <td><a href='{$delete_url}' class='button button-danger'>Delete</a></td>
              </tr>";
        $row_number++; // Increment row number
    }
    echo '</tbody>';
    echo '</table>';
}
