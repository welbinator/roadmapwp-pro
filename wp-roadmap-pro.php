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

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'ROADMAPWP_PRO_STORE_URL', 'https://roadmapwp.com' ); // IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system
// the download ID. This is the ID of your product in EDD and should match the download ID visible in your Downloads list (see example below)
define( 'ROADMAPWP_PRO_ITEM_ID', 168 ); // IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system
// the name of the product in Easy Digital Downloads
define( 'ROADMAPWP_PRO_ITEM_NAME', 'RoadMapWP' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file
// the name of the settings page for the license input to be displayed
define( 'ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE', 'roadmapwp-license' );

if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater if it doesn't already exist 
	include dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php';
}

// retrieve our license key from the DB
$license_key = trim( get_option( 'roadmapwp_pro_license_key' ) ); 
// setup the updater
$edd_updater = new EDD_SL_Plugin_Updater( ROADMAPWP_PRO_STORE_URL, __FILE__, array(
	'version' 	=> '1.0.1',		// current version number
	'license' 	=> $license_key,	// license key (used get_option above to retrieve from DB)
	'item_id'       => ROADMAPWP_PRO_ITEM_ID,	// id of this plugin
	'author' 	=> 'James Welbes',	// author of this plugin
        'beta'          => false                // set to true if you wish customers to receive update notifications of beta releases
) );

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







