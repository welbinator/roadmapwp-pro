<?php

namespace RoadMapWP\Pro\Admin\Functions;

/**
 * Checks if the 'new_idea_form' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function check_for_new_idea_form_shortcode() {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'new_idea_form' ) ) {
		update_option( 'wp_roadmap_new_idea_form_shortcode_loaded', true );
	}
}
add_action( 'wp', __NAMESPACE__ . '\\check_for_new_idea_form_shortcode' );

/**
 * Checks if the 'display_ideas' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function check_for_ideas_shortcode() {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'display_ideas' ) ) {
		update_option( 'wp_roadmap_ideas_shortcode_loaded', true );
	}
}
add_action( 'wp', __NAMESPACE__ . '\\check_for_ideas_shortcode' );

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function check_for_roadmap_shortcode() {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'roadmap' ) ) {
		update_option( 'wp_roadmap_roadmap_shortcode_loaded', true );
	}
}
add_action( 'wp', __NAMESPACE__ . '\\check_for_roadmap_shortcode' );

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function check_for_single_idea_shortcode() {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'roadmap' ) ) {
		update_option( 'wp_roadmap_single_idea_shortcode_loaded', true );
	}
}
add_action( 'wp', __NAMESPACE__ . '\\check_for_single_idea_shortcode' );

/**
 * Enqueues admin styles for specific admin pages and post types.
 *
 * @param string $hook The current admin page hook.
 */
function enqueue_admin_styles( $hook ) {
	global $post;

	// Enqueue CSS for 'idea' post type editor
	if ( 'post.php' == $hook && isset( $post ) && 'idea' == $post->post_type ) {
		$css_url = plugin_dir_url( __FILE__ ) . 'assets/css/idea-editor-styles.css';
		wp_enqueue_style( 'wp-roadmap-idea-admin-styles', $css_url );
	}

	// Enqueue CSS for taxonomies admin page
	if ( $hook === 'roadmap_page_wp-roadmap-taxonomies' ) {
		$css_url = plugin_dir_url( __FILE__ ) . 'assets/css/admin-styles.css';
		wp_enqueue_style( 'wp-roadmap-general-admin-styles', $css_url );
	}

	// Enqueue CSS for help page
	if ( $hook === 'roadmap_page_wp-roadmap-help' ) {
		$tailwind_css_url = plugin_dir_url( __FILE__ ) . '../dist/styles.css';
		wp_enqueue_style( 'wp-roadmap-tailwind-styles', $tailwind_css_url );
	}

	// Enqueue JS for the 'Taxonomies' admin page
	if ( 'roadmap_page_wp-roadmap-taxonomies' == $hook ) {
		wp_enqueue_script( 'wp-roadmap-taxonomies-js', plugin_dir_url( __FILE__ ) . 'assets/js/taxonomies.js', array( 'jquery' ), null, true );
		wp_localize_script(
			'wp-roadmap-taxonomies-js',
			'roadmapwpAjax',
			array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'delete_taxonomy_nonce' => wp_create_nonce( 'wp_roadmap_delete_taxonomy_nonce' ),
				'delete_terms_nonce'    => wp_create_nonce( 'wp_roadmap_delete_terms_nonce' ),
			)
		);
	}
	// Enqueue JS for the help page
    if ( $hook === 'roadmap_page_wp-roadmap-help' ) {
        $js_url = plugin_dir_url( __FILE__ ) . 'assets/js/admin.js';
        wp_enqueue_script( 'wp-roadmap-admin-js', $js_url, array(), null, true );
    }

	if ($hook == 'roadmap_page_wp-roadmap-settings') {
        wp_enqueue_style('wp-roadmap-select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
        wp_enqueue_script('wp-roadmap-select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js', array('jquery'), '4.0.13');

        // This script initializes Select2 for your specific select field.
        wp_add_inline_script('wp-roadmap-select2-js', "jQuery(document).ready(function($) { $('.wp-roadmap-select2').select2(); });");
    }

}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_styles' );

/**
 * Enqueues front end styles and scripts for the plugin.
 *
 * This function checks whether any of the plugin's shortcodes are loaded or if it's a singular 'idea' post,
 * and enqueues the necessary styles and scripts.
 */
function enqueue_frontend_styles() {
	global $post;

	// Initialize flags
	$has_new_idea_form_shortcode = false;
	$has_display_ideas_shortcode = false;
	$has_roadmap_shortcode       = false;
	$has_roadmap_tabs_shortcode  = false;
	$has_single_idea_shortcode   = false;

	// Initialize flags
    $has_block = $has_shortcode = false;

    // Ensure $post is a valid WP_Post object and post_content is not null before checking for blocks or shortcodes
    if ( is_a( $post, 'WP_Post' ) && !is_null( $post->post_content ) ) {
        
        // Check for block presence
        $has_block = has_block( 'roadmapwp-pro/new-idea-form', $post ) ||
                     has_block( 'roadmapwp-pro/display-ideas', $post ) ||
                     has_block( 'roadmapwp-pro/roadmap-block', $post ) ||
                     has_block( 'roadmapwp-pro/roadmap-tabs-block', $post ) ||
                     has_block( 'roadmapwp-pro/single-idea', $post );

        // Check for shortcode presence
        $has_shortcode = has_shortcode( $post->post_content, 'new_idea_form' ) ||
                         has_shortcode( $post->post_content, 'display_ideas' ) ||
                         has_shortcode( $post->post_content, 'roadmap' ) ||
                         has_shortcode( $post->post_content, 'single_idea' ) ||
                         has_shortcode( $post->post_content, 'roadmap_tabs' );
    }

	// Enqueue styles if a shortcode or block is loaded
	if ( $has_block || $has_shortcode || is_singular('idea') ) {
		// Enqueue Tailwind CSS
		$tailwind_css_url = plugin_dir_url( __FILE__ ) . '../dist/styles.css';
		wp_enqueue_style( 'wp-roadmap-tailwind-styles', $tailwind_css_url );

		// Enqueue your custom frontend styles
		$custom_css_url = plugin_dir_url( __FILE__ ) . 'assets/css/wp-roadmap-frontend.css';
		wp_enqueue_style( 'wp-roadmap-frontend-styles', $custom_css_url );

		// Enqueue scripts and localize them as before
		wp_enqueue_script( 'wp-roadmap-voting', plugin_dir_url( __FILE__ ) . 'assets/js/voting.js', array( 'jquery' ), null, true );
		wp_localize_script(
			'wp-roadmap-voting',
			'RoadMapWPVotingAjax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp-roadmap-vote-nonce' ),
			)
		);

		wp_enqueue_script( 'wp-roadmap-idea-filter', plugin_dir_url( __FILE__ ) . 'assets/js/idea-filter.js', array( 'jquery' ), '', true );
		wp_localize_script(
			'wp-roadmap-idea-filter',
			'RoadMapWPFilterAjax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp-roadmap-idea-filter-nonce' ),
			)
		);

		wp_enqueue_script( 'wp-roadmap-admin-frontend', plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js', array( 'jquery' ), '', true );
		wp_localize_script(
			'wp-roadmap-admin-frontend',
			'RoadMapWPAdminFrontendAjax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp-roadmap-admin-frontend-nonce' ),
			)
		);
	}
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_frontend_styles' );


/**
 * Adds admin menu pages for the plugin.
 *
 * This function creates a top-level menu item 'RoadMap' in the admin dashboard,
 * along with several submenu pages like Settings, Ideas, and Taxonomies.
 */
function add_admin_menu() {
	add_menu_page(
		__( 'RoadMap', 'roadmapwp-pro' ),
		__( 'RoadMap', 'roadmapwp-pro' ),
		'manage_options',
		'roadmapwp-pro',
		'edit.php?post_type=idea',
		'dashicons-chart-line',
		6
	);

	add_submenu_page(
		'roadmapwp-pro',
		__( 'Ideas', 'roadmapwp-pro' ),
		__( 'Ideas', 'roadmapwp-pro' ),
		'manage_options',
		'edit.php?post_type=idea'
	);

	add_submenu_page(
		'roadmapwp-pro',
		__( 'Settings', 'roadmapwp-pro' ),
		__( 'Settings', 'roadmapwp-pro' ),
		'manage_options',
		'wp-roadmap-settings',
		'RoadMapWP\Pro\Admin\Pages\display_settings_page'
	);

	add_submenu_page(
		'roadmapwp-pro', // parent slug
		__( 'Taxonomies', 'roadmapwp-pro' ), // page title
		__( 'Taxonomies', 'roadmapwp-pro' ), // menu title
		'manage_options', // capability
		'wp-roadmap-taxonomies', // menu slug
		'RoadMapWP\Pro\Admin\Pages\display_taxonomies_page' // function to display the page
	);

	if ( !function_exists( 'gutenberg_market_licensing' ) ) {
	add_submenu_page(
		'roadmapwp-pro',
		__( 'License', 'roadmapwp-pro' ),
		__( 'License', 'roadmapwp-pro' ),
		'manage_options',
		'roadmapwp-license',
		'RoadMapWP\Pro\Admin\Pages\license_page'
	);
	}

	add_submenu_page(
		'roadmapwp-pro',
		__( 'Help', 'roadmapwp-pro' ),
		__( 'Help', 'roadmapwp-pro' ),
		'manage_options',
		'wp-roadmap-help',
		'RoadMapWP\Pro\Admin\Pages\display_help_page'
	);

	remove_submenu_page( 'roadmapwp-pro', 'roadmapwp-pro' );
}
add_action( 'admin_menu', __NAMESPACE__ . '\\add_admin_menu' );

/**
 * Dynamically enables or disables comments on 'idea' post types.
 *
 * @param bool $open Whether the comments are open.
 * @param int  $post_id The post ID.
 * @return bool Modified status of comments open.
 */
function filter_comments_open( $open, $post_id ) {
	$post    = get_post( $post_id );
	$options = get_option( 'wp_roadmap_settings' );

	if ( $post->post_type == 'idea' ) {
		return isset( $options['allow_comments'] ) && $options['allow_comments'] == 1;
	}
	return $open;
}
add_filter( 'comments_open', __NAMESPACE__ . '\\filter_comments_open', 10, 2 );

function redirect_single_idea( $template ) {
	global $post;

	if ( 'idea' === $post->post_type ) {
		$options             = get_option( 'wp_roadmap_settings' );
		$single_idea_page_id = isset( $options['single_idea_page'] ) ? $options['single_idea_page'] : '';
		$chosen_template     = isset( $options['single_idea_template'] ) ? $options['single_idea_template'] : 'plugin';

	}

	return $template;
}

add_filter( 'single_template', __NAMESPACE__ . '\\redirect_single_idea' );

// Check if the idea has at least one vote
function get_idea_class_with_votes($idea_id) {
    
    $current_votes = get_post_meta($idea_id, 'idea_votes', true) ?: 0;
    $has_votes = $current_votes > 0;

    // Define the class based on whether the idea has votes
    $idea_class = $has_votes ? 'has-votes' : '';

    return $idea_class;
}

// Checks if LearnDash is active for use in edit.js
function check_if_learndash_is_active() {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    if ( is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
        wp_localize_script( 'roadmapwp-pro-display-ideas-block', 'learndashIsActive', array('active' => true) );
		wp_localize_script( 'roadmapwp-pro-new-idea-form-block', 'learndashIsActive', array('active' => true) );
		wp_localize_script( 'roadmapwp-pro-roadmap-block', 'learndashIsActive', array('active' => true) );
		wp_localize_script( 'roadmapwp-pro-roadmap-tabs-block', 'learndashIsActive', array('active' => true) );
		wp_localize_script( 'roadmapwp-pro-single-idea-block', 'learndashIsActive', array('active' => true) );
    } else {
        wp_localize_script( 'roadmapwp-pro-display-ideas-block', 'learndashIsActive', array('active' => false) );
		wp_localize_script( 'roadmapwp-pro-new-idea-form-block', 'learndashIsActive', array('active' => false) );
		wp_localize_script( 'roadmapwp-pro-roadmap-block', 'learndashIsActive', array('active' => false) );
		wp_localize_script( 'roadmapwp-pro-roadmap-tabs-block', 'learndashIsActive', array('active' => false) );
		wp_localize_script( 'roadmapwp-pro-single-idea-block', 'learndashIsActive', array('active' => false) );
    }
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\check_if_learndash_is_active' );


// restricts voting 
add_filter('roadmapwp_can_user_vote', function ($can_vote, $user_id) {
    $options = get_option('wp_roadmap_settings', []);
    $restrict_voting_to_logged_in_users = isset($options['restrict_voting']) ? $options['restrict_voting'] : '';
    $restricted_courses = isset($options['restricted_courses']) ? $options['restricted_courses'] : [];

    // Check if restriction to logged-in users is enabled
    if ($restrict_voting_to_logged_in_users && !is_user_logged_in()) {
        return false; // User must be logged in to vote, immediately return false if not logged in
    }

    // If LearnDash is active and course restriction is set
    if (function_exists('sfwd_lms_has_access') && !empty($restricted_courses)) {
        $user_enrolled_in_courses = false;
        foreach ($restricted_courses as $course_id) {
            if (sfwd_lms_has_access($course_id, $user_id)) {
                $user_enrolled_in_courses = true;
                break; // User is enrolled in at least one of the specified courses
            }
        }
        if (!$user_enrolled_in_courses) {
            return false; // User is not enrolled in any of the restricted courses, disallow voting
        }
    }

    // If none of the restrictions apply, or user meets all criteria, allow voting
    return $can_vote;
}, 10, 2);