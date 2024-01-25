<?php
/**
 * This file contains functionality for setting the default status of new ideas in the RoadMapWP Pro plugin.
 *
 * @package RoadMapWP\Pro\Settings\IdeaDefaultPostStatus
 */

namespace RoadMapWP\Pro\Settings\IdeaDefaultPostStatus;

/**
 * Adds a setting to the settings page for setting the default status of new ideas.
 *
 * @param string $content The existing content in the settings page.
 * @return string Modified settings content with the default status setting.
 */
function default_idea_status_setting( $content ) {
	// Fetch the current settings
	$options            = get_option( 'wp_roadmap_settings', array() );
	$default_wp_post_status = isset( $options['default_wp_post_status'] ) ? $options['default_wp_post_status'] : 'pending';

	// Create the HTML for the dropdown
	$html     = '<select name="wp_roadmap_settings[default_wp_post_status]">';
	$statuses = array(
		'publish' => 'Publish',
		'pending' => 'Pending Review',
		'draft'   => 'Draft',
	);
	foreach ( $statuses as $value => $label ) {
		$selected = selected( $default_wp_post_status, $value, false );
		$html    .= "<option value='{$value}' {$selected}>{$label}</option>";
	}
	$html .= '</select>';

	return $html;
}

add_filter( 'wp_roadmap_default_idea_status_setting', __NAMESPACE__ . '\\default_idea_status_setting' );
