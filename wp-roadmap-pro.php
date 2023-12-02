<?php
/*
Plugin Name: WP Road Map Pro
Plugin URI:  https://apexbranding.design/wp-roadmap
Description: Pro version of WP Roadmap, a roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     1.0
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-roadmap-pro
*/

// Function to check if the free version is active
function is_wp_roadmap_free_active() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    return is_plugin_active('wproadmap/wp-roadmap.php'); // Replace 'wproadmap/wp-roadmap.php' with the actual path of your free plugin's main file
}

// Deactivate Pro plugin if free version isn't active
function wp_roadmap_pro_activation_check() {
    if (!is_wp_roadmap_free_active()) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', 'wp_roadmap_pro_admin_notice_free_version_missing');
    }
}
register_activation_hook(__FILE__, 'wp_roadmap_pro_activation_check');

// Admin notice for missing free version
function wp_roadmap_pro_admin_notice_free_version_missing() {
    echo '<div class="error"><p>WP Roadmap Pro requires the free version of WP Roadmap to be installed and active.</p></div>';
}

// Deactivate Pro plugin if the free version is deactivated
add_action('admin_init', 'wp_roadmap_pro_check_free_version');
function wp_roadmap_pro_check_free_version() {
    if (!is_wp_roadmap_free_active() && is_plugin_active(plugin_basename(__FILE__))) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', 'wp_roadmap_pro_admin_notice_free_version_missing');
    }
}

// returns true for enabling pro features in the free plugin
function is_wp_roadmap_pro_active() {
    return true;
}

// Include custom taxonomies feature
include_once plugin_dir_path( __FILE__ ) . 'app/features/custom-taxonomies.php';

// Include default idea status feature
include_once plugin_dir_path( __FILE__ ) . 'app/features/idea-default-status.php';

// Include choose idea template feature
include_once plugin_dir_path(__FILE__) . 'app/features/choose-idea-template.php';

// In your Pro plugin's main file or a separate PHP file in the app/blocks directory
function wp_roadmap_pro_register_blocks() {
    // Block Editor Script
    wp_register_script(
        'wp-roadmap-pro-blocks',
        plugin_dir_url(__FILE__) . 'app/blocks/blocks.js', // Path to your block's JS file
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor')
    );

    // Register each block
    $blocks = array(
        'new-idea-form' => 'wp_roadmap_new_idea_form_shortcode',
        'display-ideas' => 'wp_roadmap_display_ideas_shortcode',
        'roadmap' => 'wp_roadmap_roadmap_shortcode',
    );

    foreach ($blocks as $block_name => $callback) {
        register_block_type('wp-roadmap-pro/' . $block_name, array(
            'editor_script' => 'wp-roadmap-pro-blocks',
            'render_callback' => $callback,
        ));
    }
}

add_action('init', 'wp_roadmap_pro_register_blocks');


add_action('enqueue_block_editor_assets', 'wp_roadmap_pro_enqueue_block_editor_assets');

function wp_roadmap_pro_enqueue_block_editor_assets() {
    if (function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if ($screen && $screen->is_block_editor()) {
            wp_enqueue_script('wp-roadmap-pro-blocks');
        }
    }
}

// Show or hide new idea heading
add_filter('wp_roadmap_hide_new_idea_heading', function($hide_heading) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    return !empty($pro_options['hide_new_idea_heading']);
});

// Setting field for hiding new idea heading
add_filter('wp_roadmap_hide_new_idea_heading_setting', function($content) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    $hide_heading_checked = isset($pro_options['hide_new_idea_heading']) ? 'checked' : '';
    return '<input type="checkbox" name="wp_roadmap_pro_settings[hide_new_idea_heading]" value="1" ' . $hide_heading_checked . ' />';
});




