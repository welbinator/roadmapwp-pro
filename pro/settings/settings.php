<?php
/**
 * This file contains functionality to validate and register settings for RoadMapWP Pro.
 *
 * @package RoadMapWP\Pro\Settings
 */

namespace RoadMapWP\Pro\Settings;

/**
 * Validates and sanitizes the input settings for RoadMapWP Pro.
 *
 * @param array $input The array of input settings to be validated.
 * @return array The validated and sanitized settings.
 */
function settings_validate( $input ) {
	// error_log('Received input: ' . print_r($input, true));
	// Initialize an array to hold the validated settings
	$validated_settings = array();

	// Validate 'default_status_term'
	$status_terms = get_terms(
		array(
			'taxonomy'   => 'idea-status',
			'hide_empty' => false,
		)
	);
	$status_slugs = wp_list_pluck( $status_terms, 'slug' );
	if ( in_array( $input['default_status_term'], $status_slugs ) ) {
		$validated_settings['default_status_term'] = $input['default_status_term'];
	} else {
		add_settings_error(
			'default_status_term',
			'invalid_status_term',
			'Invalid status selected for Default Status Term for New Ideas.',
			'error'
		);
	}

	// Validate 'default_wp_post_status'
$allowed_statuses = array( 'publish', 'pending', 'draft' );
if ( isset( $input['default_wp_post_status'] ) && in_array( $input['default_wp_post_status'], $allowed_statuses ) ) {
    $validated_settings['default_wp_post_status'] = $input['default_wp_post_status'];
} else {
    add_settings_error(
        'default_wp_post_status',
        'invalid_wp_post_status',
        'Invalid WordPress post status selected.',
        'error'
    );
}


	
	// Validate 'single_idea_template'
$allowed_templates = array( 'plugin', 'page' );
if ( isset( $input['single_idea_template'] ) && in_array( $input['single_idea_template'], $allowed_templates ) ) {
    $validated_settings['single_idea_template'] = $input['single_idea_template'];

    // Validate 'single_idea_page' if 'single_idea_template' is 'page'
    if ( $input['single_idea_template'] === 'page' ) {
        $page_id = isset( $input['single_idea_page'] ) ? $input['single_idea_page'] : '';
        if ( ! empty( $page_id ) && get_post( $page_id ) ) {
            $validated_settings['single_idea_page'] = $page_id;
        } else {
            add_settings_error(
                'single_idea_page',
                'invalid_single_idea_page',
                'Invalid page selected for Single Idea.',
                'error'
            );
        }
    }
} else {
    add_settings_error(
        'single_idea_template',
        'invalid_single_idea_template',
        'Invalid template selected for Single Idea.',
        'error'
    );
}



// Validate 'restricted_courses'
$validated_settings['restricted_courses'] = array(); // Default to empty array

if ( isset( $input['restricted_courses'] ) && is_array( $input['restricted_courses'] ) ) {
    $validated_courses = array();
    foreach ( $input['restricted_courses'] as $course_id ) {
        // Ensure the course ID is numeric to prevent invalid data types
        $course_id = intval($course_id); // Convert to integer for safety
        if ( get_post_type( $course_id ) == 'sfwd-courses' ) {
            $validated_courses[] = $course_id;
        } else {
            add_settings_error(
                'restricted_courses',
                'invalid_course_id',
                "Invalid Course ID: $course_id. Please select a valid course.",
                'error'
            );
        }
    }
    $validated_settings['restricted_courses'] = $validated_courses;
}
// No else part needed, as we default to an empty array if 'restricted_courses' isn't set



	// Validate 'restrict_voting'
	$validated_settings['restrict_voting'] = ! empty( $input['restrict_voting'] ) && $input['restrict_voting'] == '1' ? 1 : 0;

	// Validate 'allow_comments'
	$validated_settings['allow_comments'] = ! empty( $input['allow_comments'] ) && $input['allow_comments'] == '1' ? 1 : 0;

	// Validate 'hide_custom_idea_heading'
	$validated_settings['hide_custom_idea_heading'] = ! empty( $input['hide_custom_idea_heading'] ) && $input['hide_custom_idea_heading'] == '1' ? 1 : 0;

	// Validate 'custom_idea_heading'
	if ( ! empty( $input['custom_idea_heading'] ) ) {
		$validated_settings['custom_idea_heading'] = sanitize_text_field( $input['custom_idea_heading'] );
	} else {
		$validated_settings['custom_idea_heading'] = '';
	}

	// Validate 'hide_display_ideas_heading'
	$validated_settings['hide_display_ideas_heading'] = isset( $input['hide_display_ideas_heading'] ) && $input['hide_display_ideas_heading'] == '1' ? '1' : '0';

	// Validate 'custom_display_ideas_heading'
	if ( ! empty( $input['custom_display_ideas_heading'] ) ) {
		$validated_settings['custom_display_ideas_heading'] = sanitize_text_field( $input['custom_display_ideas_heading'] );
	} else {
		$validated_settings['custom_display_ideas_heading'] = '';
	}
	
	// Validate 'hide_from_rest'
    $validated_settings['hide_from_rest'] = ! empty( $input['hide_from_rest'] ) && $input['hide_from_rest'] == '1' ? 1 : 0;

	// Return the array of validated settings
	return $validated_settings;
}

function register_settings() {
	register_setting( 'wp_roadmap_settings', 'wp_roadmap_settings', __NAMESPACE__ . '\\settings_validate' );
}

add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );
