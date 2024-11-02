<?php
namespace RoadMapWP\Pro\Shortcodes\NewIdeaForm;

/**
 * Filter to set the default idea status in the Pro version.
 */
function set_default_idea_status_pro($default_status) {
    $options = get_option('wp_roadmap_settings', array());
    return isset($options['default_status_term']) ? $options['default_status_term'] : $default_status;
}
add_filter('roadmapwp_new_idea_default_status', __NAMESPACE__ . '\\set_default_idea_status_pro');
