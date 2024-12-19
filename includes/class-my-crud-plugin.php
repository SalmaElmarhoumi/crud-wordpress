<?php

class My_CRUD_Plugin {
    
    // Constructor
    public function __construct() {
        // Hook into WordPress actions
        register_activation_hook(__FILE__, [$this, 'create_db_table']);
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    // Run plugin functionality
    public function run() {
        // Other functionality can go here
    }

    // Create the database table on plugin activation
    public function create_db_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'crud_items';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Add a menu page to the WordPress admin
    public function add_plugin_page() {
        add_menu_page(
            'CRUD Plugin',
            'CRUD Plugin',
            'manage_options',
            'my-crud-plugin',
            [$this, 'plugin_page_html'],
            'dashicons-list-view',
            6
        );
    }

    // Display the plugin page
    public function plugin_page_html() {
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_item'])) {
                $this->create_item($_POST['item_name'], $_POST['item_description']);
            }
            if (isset($_POST['delete_item'])) {
                $this->delete_item($_POST['item_id']);
            }
        }

        ?>
        <div class="wrap">
            <h1>CRUD Plugin</h1>
            <form method="post">
                <label for="item_name">Item Name:</label>
                <input type="text" id="item_name" name="item_name" required>
                <label for="item_description">Description:</label>
                <textarea id="item_description" name="item_description" required></textarea>
                <input type="submit" name="add_item" value="Add Item">
            </form>

            <h2>Items List</h2>
            <?php $this->display_items(); ?>
        </div>
        <?php
    }

    // Create an item (Insert into database)
    public function create_item($name, $description) {
        global $wpdb;

        // Validate and sanitize input
        $name = sanitize_text_field($name);
        $description = sanitize_textarea_field($description);

        $wpdb->insert(
            $wpdb->prefix . 'crud_items',
            [
                'name' => $name,
                'description' => $description
            ],
            ['%s', '%s'] // Data formats
        );
    }

    // Display items (Read from database)
    public function display_items() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'crud_items';
        $items = $wpdb->get_results("SELECT * FROM $table_name");

        echo '<table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($items as $item) {
            echo '<tr>';
            echo '<td>' . esc_html($item->id) . '</td>';
            echo '<td>' . esc_html($item->name) . '</td>';
            echo '<td>' . esc_html($item->description) . '</td>';
            echo '<td><form method="POST"><input type="hidden" name="item_id" value="' . $item->id . '"><input type="submit" name="delete_item" value="Delete" onclick="return confirm(\'Are you sure?\');"></form></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    // Delete an item (from the database)
    public function delete_item($id) {
        global $wpdb;

        // Validate and sanitize input
        $id = intval($id);

        $wpdb->delete(
            $wpdb->prefix . 'crud_items',
            ['id' => $id],
            ['%d'] // Data format
        );
    }

    // Enqueue scripts and styles (optional)
    public function enqueue_scripts($hook) {
        if ($hook != 'toplevel_page_my-crud-plugin') {
            return;
        }

        wp_enqueue_style('my-crud-plugin-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
        wp_enqueue_script('my-crud-plugin-script', plugin_dir_url(__FILE__) . 'assets/js/script.js');
    }
}

