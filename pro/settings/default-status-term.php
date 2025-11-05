<?php
/**
 * This file handles the registration of default status settings in the RoadMapWP Pro plugin.
 *
 * @package RoadMapWP\Pro\Settings\DefaultStatus
 */

namespace RoadMapWP\Pro\Settings\DefaultStatus;

/**
 * Registers the default status setting for the RoadMapWP Pro plugin.
 */
function register_default_status_setting() {
	// Use an args array for register_setting so PHPStan can validate the shape (sanitize_callback key expected).
	register_setting(
		'wp_roadmap_settings',
		'wp_roadmap_settings',
		array(
			// Use a closure that delegates to the real settings_validate function so PHPStan sees a proper callable shape.
			'sanitize_callback' => function ( $input ) {
				return \RoadMapWP\Pro\Settings\settings_validate( $input );
			},
		)
	);
}

add_action( 'admin_init', __NAMESPACE__ . '\\register_default_status_setting' );
