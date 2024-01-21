<?php
/*
Plugin Name: RoadMapWP Pro
Plugin URI:  https://apexbranding.design/wp-roadmap
Description: Pro version of WP Roadmap, a roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     2.0.1
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: roadmapwp-pro
*/


// This function will be called when the Pro version is activated.
function wp_roadmap_pro_activate() {
	// Check if the free version is active
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( is_plugin_active( 'roadmapwp-free/wp-roadmap.php' ) ) {
		// Deactivate the free version
		deactivate_plugins( 'roadmapwp-free/wp-roadmap.php' );
	}
	// Additional activation code for Pro version goes here...
}

define( 'ROADMAPWP_PRO_MAIN_FILE', __FILE__ );

// Register the activation hook for the Pro version
register_activation_hook( __FILE__, 'wp_roadmap_pro_activate' );

// Include EDD Licensing file
require_once 'EDD_Licensing.php';

// Include pro settings
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/settings.php';

// Include enable comments feature
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/comments.php';

// Include custom taxonomies feature
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/custom-taxonomies.php';

// Include default idea status feature
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/idea-default-status.php';

// Include choose idea template feature
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/choose-idea-template.php';

// Include blocks
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/blocks.php';

// Include roadmap block
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/roadmap-block.php';

// Include roadmap tabs block
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/roadmap-tabs-block.php';

// Include new idea form block
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/new-idea-form-block.php';

// Include custom submit idea heading setting
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/submit-idea-custom-heading.php';

// Include custom submit idea heading setting
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/display-ideas-custom-heading.php';

// Include default idea status setting
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/default-status-term.php';

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'app/admin-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'app/cpt-ideas.php';
require_once plugin_dir_path( __FILE__ ) . 'app/ajax-handlers.php';
require_once plugin_dir_path( __FILE__ ) . 'app/admin-pages.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/new-idea-form.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/display-ideas.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/roadmap.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/roadmap-tabs.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/single-idea.php';

function wp_roadmap_pro_on_activation() {
	// Directly call the function that registers your taxonomies here
	\RoadMapWP\Pro\register_default_idea_taxonomies();

	// Now add the terms
	$status_terms = array( 'New Idea', 'Maybe', 'Up Next', 'On Roadmap', 'Not Now', 'Closed' );
	foreach ( $status_terms as $term ) {
		if ( ! term_exists( $term, 'status' ) ) {
			$result = wp_insert_term( $term, 'status' );
			if ( is_wp_error( $result ) ) {
				error_log( 'Error inserting term ' . $term . ': ' . $result->get_error_message() );
			}
		}
	}
}

register_activation_hook( __FILE__, 'wp_roadmap_pro_on_activation' );

function wp_roadmap_pro_custom_template( $template ) {
	global $post;

	if ( 'idea' === $post->post_type ) {
		$pro_options          = get_option( 'wp_roadmap_pro_settings' );
		$chosen_idea_template = isset( $pro_options['single_idea_template'] ) ? $pro_options['single_idea_template'] : 'plugin';

		if ( $chosen_idea_template === 'plugin' && file_exists( plugin_dir_path( __FILE__ ) . 'app/templates/template-single-idea.php' ) ) {
			return plugin_dir_path( __FILE__ ) . 'app/templates/template-single-idea.php';
		}
	}

	return $template;
}

add_filter( 'single_template', 'wp_roadmap_pro_custom_template' );

function wp_roadmap_pro_log_all_status_terms() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'status',
			'hide_empty' => false,
		)
	);
}
add_action( 'init', 'wp_roadmap_pro_log_all_status_terms' );
