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
