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
	if ( in_array( $input['default_wp_post_status'], $allowed_statuses ) ) {
		$validated_settings['default_wp_post_status'] = $input['default_wp_post_status'];
	} else {
		add_settings_error(
			'default_wp_post_status',
			'invalid_wp_post_status',
			'Invalid WordPress post status selected.',
			'error'
		);
	}

	// Validate 'default_wp_post_status'
	$allowed_statuses = array( 'publish', 'pending', 'draft' );
	if ( in_array( $input['default_wp_post_status'], $allowed_statuses ) ) {
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
	if ( in_array( $input['single_idea_template'], $allowed_templates ) ) {
		$validated_settings['single_idea_template'] = $input['single_idea_template'];

		// Validate 'single_idea_page' if 'single_idea_template' is 'page'
		if ( $input['single_idea_template'] === 'page' ) {
			$page_id = $input['single_idea_page'];
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

	// Return the array of validated settings
	return $validated_settings;
}

function register_settings() {
	register_setting( 'wp_roadmap_settings', 'wp_roadmap_settings', __NAMESPACE__ . '\\settings_validate' );
}

add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );
