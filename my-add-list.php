<?php
/*
Plugin Name: My Add List
Plugin URI: cgdiomampo.dev
Description: A simple plugin to manage and display lists using shortcodes
Version: 1.0
Author: Christian Diomampo
*/

/* to use the shortcode, use [myaddlist] */

if (!defined('ABSPATH')) {
    exit;
}

function myadd_admin_menu() {
    add_menu_page(
        'My Add List Settings',
        'My Add List',
        'manage_options',
        'myadd-settings',
        'myadd_settings_page',
        'dashicons-list-view'
    );
}
add_action('admin_menu', 'myadd_admin_menu');

function myadd_register_settings() {
    register_setting('myadd_options', 'myadd_items');
}
add_action('admin_init', 'myadd_register_settings');

function myadd_admin_scripts($hook) {
    if ($hook != 'toplevel_page_myadd-settings') {
        return;
    }
    
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('myadd-admin', plugins_url('js/admin.js', __FILE__), array('jquery', 'jquery-ui-sortable'), '1.0', true);
    wp_enqueue_style('myadd-admin-style', plugins_url('css/admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'myadd_admin_scripts');

function myadd_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $items = get_option('myadd_items', array());
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="myadd-container">
            <form method="post" action="options.php" id="myadd-form">
                <?php settings_fields('myadd_options'); ?>
                
                <div class="myadd-items" id="sortable-list">
                    <?php
                    if (!empty($items)) {
                        foreach ($items as $key => $item) {
                            echo '<div class="myadd-item">';
                            echo '<input type="text" name="myadd_items[]" value="' . esc_attr($item) . '">';
                            echo '<button type="button" class="remove-item button">Remove</button>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                
                <div class="myadd-controls">
                    <button type="button" class="add-item button button-secondary">Add New Item</button>
                    <?php submit_button('Save Changes'); ?>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function myadd_shortcode() {
    $items = get_option('myadd_items', array());
    
    if (empty($items)) {
        return '<p>No items found.</p>';
    }
    
    $output = '<ul class="myadd-frontend">';
    foreach ($items as $item) {
        $output .= '<li>' . esc_html($item) . '</li>';
    }
    $output .= '</ul>';
    
    return $output;
}
add_shortcode('myaddlist', 'myadd_shortcode');

function myadd_activate() {
    wp_mkdir_p(plugin_dir_path(__FILE__) . 'js');
    $js_content = <<<EOT
jQuery(document).ready(function($) {
    // Make list sortable
    $('#sortable-list').sortable({
        handle: '.myadd-item',
        placeholder: 'myadd-placeholder',
        opacity: 0.7
    });

    // Add new item
    $('.add-item').on('click', function() {
        var newItem = $('<div class="myadd-item">' +
            '<input type="text" name="myadd_items[]" value="">' +
            '<button type="button" class="remove-item button">Remove</button>' +
            '</div>');
        $('#sortable-list').append(newItem);
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).parent('.myadd-item').remove();
    });
});
EOT;
    file_put_contents(plugin_dir_path(__FILE__) . 'js/admin.js', $js_content);

    wp_mkdir_p(plugin_dir_path(__FILE__) . 'css');
    $css_content = <<<EOT
.myadd-item {
    padding: 10px;
    background: #fff;
    border: 1px solid #ddd;
    margin-bottom: 5px;
    cursor: move;
}

.myadd-placeholder {
    border: 1px dashed #ccc;
    height: 40px;
    margin-bottom: 5px;
}

.myadd-item input[type="text"] {
    width: calc(100% - 100px);
    margin-right: 10px;
}

.myadd-controls {
    margin-top: 20px;
}

.add-item {
    margin-right: 10px;
}
EOT;
    file_put_contents(plugin_dir_path(__FILE__) . 'css/admin.css', $css_content);
}
register_activation_hook(__FILE__, 'myadd_activate');