<?php
/*
Plugin Name: WP Road Map Pro
Plugin URI:  https://apexbranding.design/wp-roadmap
Description: Pro version of WP Roadmap, a roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     1.0.5
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-roadmap-pro
*/

// Function to check if the free version is active
function is_wp_roadmap_free_installed() {
    // Check for a unique function or class from the free version
    if (function_exists('wp_roadmap_free_version_active')) {
        return true;
    }

    // Fallback to check the database option
    return get_option('wp_roadmap_free_active', false);
}

// Deactivate Pro plugin if free version isn't active
function wp_roadmap_pro_activation_check() {
    if (!is_wp_roadmap_free_active()) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', 'wp_roadmap_pro_admin_notice_free_version_missing');
    }
}
register_activation_hook(__FILE__, 'wp_roadmap_pro_activation_check');

function is_wp_roadmap_free_active() {
    return function_exists('wp_roadmap_free_version_active');
}

function wp_roadmap_pro_admin_notice_free_version_missing() {
    echo '<div class="error"><p>' . esc_html__('WP Roadmap Pro requires the free version to be installed and active.', 'wp-roadmap-pro') . '</p></div>';
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


// Include EDD Licensing
include_once plugin_dir_path( __FILE__ ) . 'EDD_Licensing.php';

// Include pro settings
include_once plugin_dir_path( __FILE__ ) . 'app/settings/settings.php';

// Include enable comments feature
include_once plugin_dir_path( __FILE__ ) . 'app/settings/comments/comments.php';

// Include custom taxonomies feature
include_once plugin_dir_path( __FILE__ ) . 'app/settings/custom-taxonomies/custom-taxonomies.php';

// Include default idea status feature
include_once plugin_dir_path( __FILE__ ) . 'app/settings/idea-default-status/idea-default-status.php';

// Include choose idea template feature
include_once plugin_dir_path( __FILE__ ) . 'app/settings/choose-idea-template/choose-idea-template.php';

// Include blocks
include_once plugin_dir_path( __FILE__ ) . 'app/blocks/blocks.php';

// Include custom submit idea heading setting
include_once plugin_dir_path( __FILE__ ) . 'app/settings/submit-idea-custom-heading/submit-idea-custom-heading.php';

// Include custom submit idea heading setting
include_once plugin_dir_path( __FILE__ ) . 'app/settings/display-ideas-custom-heading/display-ideas-custom-heading.php';







