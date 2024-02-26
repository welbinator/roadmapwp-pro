<?php
/**
 * This file contains functions to register and enqueue block editor assets for the RoadMapWP Pro plugin.
 * It includes the registration of custom blocks and enqueuing of necessary scripts for the block editor.
 */

namespace RoadMapWP\Pro\Blocks;

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
				plugin_dir_url( __FILE__ ) . '../../build/roadmap-block.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
				filemtime( plugin_dir_path( __DIR__ ) . '../../build/roadmap-block.js' )
			);

			// Enqueue the Roadmap Tabs block editor script
			wp_enqueue_script(
				'roadmapwp-pro-roadmap-tabs-block',
				plugin_dir_url( __FILE__ ) . '../../build/roadmap-tabs-block.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
				filemtime( plugin_dir_path( __DIR__ ) . '../../build/roadmap-tabs-block.js' )
			);

			wp_enqueue_script(
				'roadmapwp-pro-new-idea-form-block',
				plugin_dir_url( __FILE__ ) . '../../build/new-idea-form-block.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
				filemtime( plugin_dir_path( __DIR__ ) . '../../build/new-idea-form-block.js' )
			);

			wp_enqueue_script(
				'roadmapwp-pro-display-ideas-block',
				plugin_dir_url( __FILE__ ) . '../../build/display-ideas-block/index.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
				filemtime( plugin_dir_path( __DIR__ ) . '../../build/display-ideas-block/index.js' )
			);

			wp_enqueue_script(
				'roadmapwp-pro-single-idea-block',
				plugin_dir_url( __FILE__ ) . '../../build/single-idea-block/index.js',
				array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
				filemtime( plugin_dir_path( __DIR__ ) . '../../build/single-idea-block/index.js' )
			);
		}
	}
}

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );

// add blocks category
function add_block_category( $categories, $post ) {
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'roadmap',
                'title' => __( 'Roadmap', 'roadmapwp-pro' ),
                
            ),
        )
    );
}
add_filter( 'block_categories_all', __NAMESPACE__ . '\\add_block_category', 10, 2 );

