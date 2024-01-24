<?php
/**
 * This file handles the registration of default status settings in the RoadMapWP Pro plugin.
 *
 * @package RoadMapWP\Pro
 */

namespace RoadMapWP\Pro;

/**
 * Registers the default status setting for the RoadMapWP Pro plugin.
 */
function register_default_status_setting() {
    register_setting( 'wp_roadmap_settings', 'wp_roadmap_settings', 'RoadMapWP\Pro\Settings\settings_validate' );
}

add_action( 'admin_init', __NAMESPACE__ . '\\register_default_status_setting' );
