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

// Include blocks
include_once plugin_dir_path(__FILE__) . 'app/blocks/blocks.php';




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


// Filter for custom idea heading text
add_filter('wp_roadmap_custom_idea_heading_text', function($default_heading) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    return !empty($pro_options['new_idea_heading']) ? $pro_options['new_idea_heading'] : $default_heading;
});

// Filter for adding new heading text field in settings
add_filter('wp_roadmap_hide_new_idea_heading_setting', function($content) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    $hide_heading_checked = isset($pro_options['hide_new_idea_heading']) ? 'checked' : '';
    $new_heading = isset($pro_options['new_idea_heading']) ? $pro_options['new_idea_heading'] : '';

    $content = '<input type="checkbox" name="wp_roadmap_pro_settings[hide_new_idea_heading]" value="1" ' . $hide_heading_checked . ' />';
    $content .= '<br/><label for="new_idea_heading">New Heading:</label>';
    $content .= '<input type="text" name="wp_roadmap_pro_settings[new_idea_heading]" value="' . esc_attr($new_heading) . '" />';

    return $content;
});

function wp_roadmap_pro_save_settings() {
    if (isset($_POST['wp_roadmap_pro_settings'])) {
        check_admin_referer('wp_roadmap_pro_settings_action', 'wp_roadmap_pro_settings_nonce');

        $pro_settings = get_option('wp_roadmap_pro_settings', []);

        // Capture the 'hide_new_idea_heading' checkbox value
        $pro_settings['hide_new_idea_heading'] = isset($_POST['wp_roadmap_pro_settings']['hide_new_idea_heading']) ? 1 : 0;

        // Capture the 'new_idea_heading' text value
        $pro_settings['new_idea_heading'] = sanitize_text_field($_POST['wp_roadmap_pro_settings']['new_idea_heading']);

        update_option('wp_roadmap_pro_settings', $pro_settings);
    }
}
add_action('admin_init', 'wp_roadmap_pro_save_settings');




