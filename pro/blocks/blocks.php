<?php
/**
 * This file contains functions to register and enqueue block editor assets for the RoadMapWP Pro plugin.
 * It includes the registration of custom blocks and enqueuing of necessary scripts for the block editor.
 */

namespace RoadMapWP\Pro;

/**
 * Registers custom blocks for the RoadMapWP Pro plugin.
 * 
 * This function registers scripts used by the blocks and the blocks themselves, setting up render callbacks as necessary.
 */
function register_blocks() {
	// Block Editor Script
	wp_register_script(
		'roadmapwp-pro-blocks',
		plugin_dir_url( __FILE__ ) . 'blocks.js', // Path to your block's JS file
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' )
	);

	// Register each block
	$blocks = array(
		'display-ideas' => 'RoadMapWP\Pro\Shortcodes\display_ideas_shortcode',
	);

	register_block_type(
		'roadmapwp-pro/single-idea',
		array(
			'editor_script'   => 'roadmapwp-pro-blocks',
			'render_callback' => function ( $atts ) {
				return \RoadMapWP\Shortcodes\single_idea_shortcode( $atts, true );
 // Passing true for the $is_block parameter
			},
		)
	);

	foreach ( $blocks as $block_name => $callback ) {
		register_block_type(
			'roadmapwp-pro/' . $block_name,
			array(
				'editor_script'   => 'roadmapwp-pro-blocks',
				'render_callback' => $callback,
			)
		);
	}
}

add_action( 'init', 'RoadMapWP\Pro\register_blocks' );


/**
 * Enqueues block editor assets for the RoadMapWP Pro plugin.
 * 
 * This function checks if the current screen is the block editor and enqueues scripts for the custom blocks.
 */
function enqueue_block_editor_assets() {
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( $screen && $screen->is_block_editor() ) {
			// Enqueue the existing script
			wp_enqueue_script( 'roadmapwp-pro-blocks' );

			// Enqueue the Roadmap block editor script
			wp_enqueue_script(
				'roadmapwp-pro-roadmap-block',
				plugin_dir_url( __FILE__ ) . 'build/roadmap-block.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
				filemtime( plugin_dir_path( __FILE__ ) . 'build/roadmap-block.js' )
			);

			// Enqueue the Roadmap Tabs block editor script
			wp_enqueue_script(
				'roadmapwp-pro-roadmap-tabs-block',
				plugin_dir_url( __FILE__ ) . 'build/roadmap-tabs-block.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
				filemtime( plugin_dir_path( __FILE__ ) . 'build/roadmap-tabs-block.js' )
			);

			wp_enqueue_script(
				'roadmapwp-pro-new-idea-form-block',
				plugin_dir_url( __FILE__ ) . 'build/new-idea-form-block.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
				filemtime( plugin_dir_path( __FILE__ ) . 'build/new-idea-form-block.js' )
			);
		}
	}
}

add_action( 'enqueue_block_editor_assets', 'RoadMapWP\Pro\enqueue_block_editor_assets' );
