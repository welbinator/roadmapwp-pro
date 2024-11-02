<?php
/**
 * This file contains functions related to the registration and rendering of the 'Display Ideas' block in the RoadMapWP Pro plugin.
 * It ensures that the block is registered correctly in WordPress and rendered using the corresponding shortcode function.
 *
 * @package RoadMapWP\Pro\Blocks\DisplayIdeas
 */

namespace RoadMapWP\Pro\Blocks\DisplayIdeas;

use RoadMapWP\Pro\Admin\Functions;

/**
 * Initializes the 'Display Ideas' block.
 *
 * Registers the block using metadata loaded from the `block.json` file.
 * Sets the render callback to the `block_render` function.
 */
function register_block() {

	$display_ideas_block_path = plugin_dir_path( dirname( __DIR__ ) ) . 'build/display-ideas-block';
	register_block_type_from_metadata(
		$display_ideas_block_path,
		array(
            'render_callback' => __NAMESPACE__ . '\block_render',
            'attributes'      => array(
                'onlyLoggedInUsers' => array(
                    'type'    => 'boolean',
                    'default' => false,
                ),
                'selectedCourses' => array(
                    'type'    => 'array',
                    'default' => array(),
                    'items'   => array(
                        'type' => 'integer',
                    ),
                ),
            ),
        )
        
	);
}
add_action( 'init', __NAMESPACE__ . '\\register_block' );

/**
 * Renders the 'Display Ideas' block.
 *
 * Outputs the HTML for the 'Display Ideas' block, including UI for filtering and displaying ideas.
 *
 * @param array $attributes The attributes of the block.
 * @return string The HTML content to display.
 */
function block_render( $attributes ) {
    $user_id = get_current_user_id();
    $display_block = apply_filters('roadmapwp_display_ideas_block', true, $attributes, $user_id);

    // Dev Note: probably a better way to do this
    $learndash_active = function_exists('sfwd_lms_has_access');

    // Check if any courses are selected
    $selectedCourses = $attributes['selectedCourses'] ?? [];
    $userHasAccess = false;

    if (!$display_block) {
        return '';
    }

    update_option( 'wp_roadmap_display_ideas_shortcode_loaded', true );

    // Block access if only logged in users can view and the user is not logged in
    if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
        return '';
    }

    // If LearnDash is active and courses are selected, check the user's enrollment
    if ($learndash_active && !empty($selectedCourses)) {
        foreach ($selectedCourses as $courseId) {
            if (sfwd_lms_has_access($courseId, $user_id)) {
                $userHasAccess = true;
                break; // Exit loop if user has access to at least one course
            }
        }
        
        // If the user is not enrolled in any selected courses, return without rendering the block
        if (!$userHasAccess) {
            return '';
        }
    } elseif (!empty($selectedCourses) && !$learndash_active) {
        // If LearnDash is not active but courses were selected, ignore the course selection and proceed to render
        // This ensures the block content is accessible when LearnDash is deactivated
        $userHasAccess = true; // Bypass enrollment checks
    }

    ob_start();

    $taxonomies = array( 'idea-tag' );

    // Include custom taxonomies
    $custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
    $taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

    // Exclude 'idea-status' taxonomy
    $exclude_taxonomies = array( 'idea-status' );
    $taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );

    // Retrieve color settings
    $options = get_option( 'wp_roadmap_settings' );

    // Check if the pro version is installed and settings are enabled
    $hide_display_ideas_heading = apply_filters( 'wp_roadmap_hide_display_ideas_heading', false );
    $new_display_ideas_heading  = apply_filters( 'wp_roadmap_custom_display_ideas_heading_text', 'Browse Ideas' );

    echo '<div class="roadmap_wrapper container mx-auto">';
    echo '<div class="browse_ideas_frontend">';
    
    if ( ! $hide_display_ideas_heading ) {
        echo '<h2>' . esc_html( $new_display_ideas_heading ) . '</h2>';
    }
    
    // Flag to check if there are any terms in the taxonomies
    $show_filters = false;

    foreach ( $taxonomies as $taxonomy_slug ) {
        $taxonomy = get_taxonomy( $taxonomy_slug );
        if ( $taxonomy && $taxonomy_slug != 'idea-status' ) {
            $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));
            if (!empty($terms)) {
                // Set flag to true if there are terms
                $show_filters = true;
            }
        }
    }

    // Conditionally render the filters-wrapper div
    if ( $show_filters ) {
        echo '<div class="rmwp__filters-wrapper"><h4>Filters:</h4><div class="rmwp__filters-inner">';
        // Reiterate through taxonomies to build the filters UI
        foreach ( $taxonomies as $taxonomy_slug ) {
            $taxonomy = get_taxonomy( $taxonomy_slug );
            if ( $taxonomy && $taxonomy_slug != 'idea-status' ) {
                $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));
                echo '<div class="rmwp__ideas-filter-taxonomy" data-taxonomy="' . esc_attr( $taxonomy_slug ) . '">';
                echo '<label>' . esc_html( $taxonomy->labels->singular_name ) . ':</label>';
                echo '<div class="rmwp__taxonomy-term-labels">';
                foreach ( $terms as $term ) {
                    echo '<label class="rmwp__taxonomy-term-label"><input type="checkbox" name="idea_taxonomies[' . esc_attr( $taxonomy->name ) . '][]" value="' . esc_attr( $term->slug ) . '"> ' . esc_html( $term->name ) . '</label>';
                }
                echo '</div>';
                echo '<div class="rmwp__filter-match-type"><label><input type="radio" name="match_type_' . esc_attr( $taxonomy->name ) . '" value="any" checked> Any</label><label><input type="radio" name="match_type_' . esc_attr( $taxonomy->name ) . '" value="all"> All</label></div></div>';
            }
        }
        echo '</div></div>';
    }

    echo '</div><br />';
    
    echo '<div class="pt-2 relative mx-auto text-gray-600 flex gap-4">';
    echo '<input id="roadmap_search_input" class="grow border-2 border-gray-300 bg-white h-10 px-5 pr-16 rounded-lg text-sm focus:outline-none" type="search" name="search" placeholder="Search">';
    echo '<button id="roadmap_search_submit" type="submit" class="p-3">';
    echo '<svg class="text-white-600 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56.966 56.966" width="512px" height="512px"><path d="M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17s-17-7.626-17-17S14.61,6,23.984,6z"/></svg>';
    echo '</button></div>';

    echo '<div class="rmwp__ideas-list">';

    $args = array('post_type' => 'idea', 'posts_per_page' => -1);
    $query = new \WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">';
        while ($query->have_posts()) {
            $query->the_post();
            $idea_id = get_the_ID();
            $vote_count = intval(get_post_meta($idea_id, 'idea_votes', true));
            $idea_class = Functions\get_idea_class_with_votes($idea_id);
            echo '<div class="wp-roadmap-idea flex flex-col justify-between border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden ' . esc_attr($idea_class) . '" data-v0-t="card">';
            include plugin_dir_path(__FILE__) . '../../app/includes/display-ideas-grid.php';
            include plugin_dir_path(__FILE__) . '../../app/includes/display-ideas-admin.php';
            echo '</div>';
        }
        echo '</div></div></div>';
    } else {
        echo '<p>No ideas found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}



