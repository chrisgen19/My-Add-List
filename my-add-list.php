<?php
/*
Plugin Name: My Add List
Description: A plugin to manage and display sortable lists using shortcode
Version: 1.0
Author: Christian Diomampo
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Class
class MyAddList {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new MyAddList();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_shortcode('myaddlist', array($this, 'renderList'));
        add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'));
        add_action('wp_ajax_save_list_items', array($this, 'saveListItems'));
    }
    
    public function addAdminMenu() {
        add_menu_page(
            'My Add List',
            'My Add List',
            'manage_options',
            'my-add-list',
            array($this, 'renderAdminPage'),
            'dashicons-list-view'
        );
    }
    
    public function addAdminScripts($hook) {
        if ($hook != 'toplevel_page_my-add-list') {
            return;
        }
        
        wp_enqueue_script(
            'my-add-list-js',
            plugins_url('js/admin.js', __FILE__),
            array('jquery'),
            '1.0',
            true
        );
        
        wp_enqueue_style(
            'my-add-list-css',
            plugins_url('css/admin.css', __FILE__)
        );
        
        wp_localize_script('my-add-list-js', 'myAddListAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('my_add_list_nonce')
        ));
    }
    
    public function renderAdminPage() {
        $items = get_option('my_add_list_items', array());
        ?>
        <div class="wrap">
            <h1>My Add List</h1>
            <div class="list-manager-container">
                <div class="add-item-section">
                    <input type="text" id="new-item-text" placeholder="Enter new item">
                    <button id="add-item" class="button button-primary">Add Item</button>
                </div>
                
                <div class="items-container">
                    <h3>Current Items</h3>
                    <ul id="sortable-list">
                        <?php foreach ($items as $item): ?>
                            <li class="list-item" data-id="<?php echo esc_attr($item['id']); ?>">
                                <span class="item-text"><?php echo esc_html($item['text']); ?></span>
                                <span class="item-handle">☰</span>
                                <button class="remove-item">×</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="shortcode-info">
                    <p>Use shortcode <code>[myaddlist]</code> to display this list on any page or post.</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function saveListItems() {
        if (!check_ajax_referer('my_add_list_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $items = isset($_POST['items']) ? $_POST['items'] : array();
        $sanitized_items = array();
        
        foreach ($items as $item) {
            $sanitized_items[] = array(
                'id' => sanitize_text_field($item['id']),
                'text' => sanitize_text_field($item['text'])
            );
        }
        
        update_option('my_add_list_items', $sanitized_items);
        wp_send_json_success();
    }
    
    public function renderList($atts) {
        $items = get_option('my_add_list_items', array());
        
        if (empty($items)) {
            return '';
        }
        
        $output = '<ul class="my-add-list-items">';
        foreach ($items as $item) {
            $output .= '<li>' . esc_html($item['text']) . '</li>';
        }
        $output .= '</ul>';
        
        return $output;
    }
}

MyAddList::getInstance();

register_activation_hook(__FILE__, function() {
    if (!get_option('my_add_list_items')) {
        add_option('my_add_list_items', array());
    }
});

register_deactivation_hook(__FILE__, function() {
    delete_option('my_add_list_items');
    
    $role = get_role('administrator');
    if ($role) {
        $role->remove_cap('manage_my_add_list');
    }
    
    wp_clear_scheduled_hook('my_add_list_cleanup');
    
    delete_transient('my_add_list_cache');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'my_add_list';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    
    wp_cache_delete('my_add_list_items', 'my_add_list');
    
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/my-add-list';
    if (is_dir($plugin_upload_dir)) {
        array_map('unlink', glob("$plugin_upload_dir/*.*"));
        rmdir($plugin_upload_dir);
    }
});