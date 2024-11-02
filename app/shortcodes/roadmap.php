<?php
/**
 * Shortcode for Displaying Ideas in RoadMapWP Pro.
 *
 * This file modifies and extends the Free version of the shortcode to display additional Pro features.
 *
 * @package RoadMapWP\Pro\Shortcodes\DisplayIdeas
 */

namespace RoadMapWP\Pro\Shortcodes\DisplayIdeas;

use RoadMapWP\Pro\Admin\Functions;



// Modify the classes of individual idea divs based on votes
add_filter('roadmapwp_roadmap_shortcode_idea_classes', function ($classes, $idea_id) {
    $idea_class = Functions\get_idea_class_with_votes($idea_id);
    return $classes . ' ' . $idea_class;
}, 10, 2);

add_action('roadmapwp_roadmap_shortcode_after_idea_content', function($idea_id, $vote_count) {
        // Include Pro-specific admin controls
    if (current_user_can('administrator')) {
        include plugin_dir_path(dirname(__FILE__)) . '../pro/includes/display-ideas-admin.php';
    }
}, 10, 2); // Notice the 2 here, which specifies that 2 arguments will be passed

