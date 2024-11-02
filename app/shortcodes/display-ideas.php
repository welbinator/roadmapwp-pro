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
 
 // Modify classes of the grid container (if necessary)
 add_filter('roadmapwp_ideas_grid_classes', function($classes) {
     return $classes . ' pro-specific-class';
 });
 
 // Modify the classes of individual idea divs based on votes
 add_filter('roadmapwp_idea_classes', function($classes, $idea_id) {
     $idea_class = Functions\get_idea_class_with_votes($idea_id);
     return $classes . ' ' . $idea_class;
 }, 10, 2);
 
 // Add additional content after each idea in the grid (e.g., Pro-specific admin controls)
 add_action('roadmapwp_after_idea_content', function($idea_id) {
     include plugin_dir_path(dirname(__FILE__)) . '../pro/includes/display-ideas-admin.php';
 });
 