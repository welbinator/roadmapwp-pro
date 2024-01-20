<?php
// Ensure ABSPATH is defined for security
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Function to render the block
function display_ideas_block_render() {
	// Check if the shortcode function exists
	if ( function_exists( 'wp_roadmap_display_ideas_shortcode' ) ) {
		// Call the correct shortcode function
		return wp_roadmap_display_ideas_shortcode();
	}

	return '';
}

// Register the block
function wp_roadmap_register_display_ideas_block() {
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
add_action( 'init', 'wp_roadmap_register_display_ideas_block' );
