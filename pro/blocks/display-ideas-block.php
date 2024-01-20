<?php
/**
 * This file contains functions related to the registration and rendering of the 'Display Ideas' block in the RoadMapWP Pro plugin.
 * It ensures that the block is registered correctly in WordPress and rendered using the corresponding shortcode function.
 */

namespace RoadMapWP\Pro;

// Ensure ABSPATH is defined for security
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the 'Display Ideas' block.
 * 
 * This function checks if the corresponding shortcode function exists and returns its output.
 * 
 * @return string The output of the shortcode function or an empty string if the function doesn't exist.
 */
function display_ideas_block_render() {
	// Check if the shortcode function exists
	if ( function_exists( 'wp_roadmap_display_ideas_shortcode' ) ) {
		// Call the correct shortcode function
		return wp_roadmap_display_ideas_shortcode();
	}

	return '';
}

/**
 * Registers the 'Display Ideas' block.
 * 
 * This function registers the block type in WordPress, specifying the render callback function.
 */
function register_display_ideas_block() {
	// Check if function exists
	if ( function_exists( 'register_block_type' ) ) {
		register_block_type(
			'roadmapwp-pro/display-ideas-block',
			array(
				'render_callback' => 'display_ideas_block_render',
			)
		);
	}
}
add_action( 'init', 'RoadMapWP\Pro\\register_display_ideas_block' );
