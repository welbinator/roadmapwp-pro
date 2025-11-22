<?php

namespace RoadMapWP\Pro\Admin\Enqueue;

/**
 * Enqueue admin scripts for the RoadMapWP settings page.
 */
function enqueue_admin_scripts($hook) {
    // Only load on RoadMapWP settings page
    if ('roadmapwp-pro_page_wp-roadmap-settings' !== $hook) {
        return;
    }

    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Enqueue our custom admin settings script
    wp_enqueue_script(
        'roadmapwp-pro-admin-settings',
        plugin_dir_url(__FILE__) . 'assets/js/admin-settings.js',
        array('jquery'),
        defined('RMWP_PLUGIN_VERSION') ? RMWP_PLUGIN_VERSION : '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_scripts');