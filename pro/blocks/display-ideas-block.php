<?php
/**
 * Display Ideas block registration and renderer for RoadMapWP Pro.
 *
 * @package RoadMapWP\Pro\Blocks\DisplayIdeas
 */

namespace RoadMapWP\Pro\Blocks\DisplayIdeas;

use RoadMapWP\Pro\Admin\Functions;

/**
 * Register the block and its attributes.
 */
function register_block() {
	$display_ideas_block_path = plugin_dir_path( dirname( __DIR__ ) ) . 'build/display-ideas-block';

	register_block_type_from_metadata(
		$display_ideas_block_path,
		array(
			'render_callback' => __NAMESPACE__ . '\\block_render',
			'attributes'      => array(
				'onlyLoggedInUsers' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'selectedCourses'   => array(
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
 * Render callback for the 'Display Ideas' block.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function block_render( $attributes ) {
	$user_id       = get_current_user_id();
	$display_block = apply_filters( 'roadmapwp_display_ideas_block', true, $attributes, $user_id );

	if ( ! $display_block ) {
		return '';
	}

	// Restrict to logged-in users when requested.
	if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
		return '';
	}

	// LearnDash integration: check access when courses are selected.
	$learndash_active = function_exists( 'sfwd_lms_has_access' );
	$selected_courses = isset( $attributes['selectedCourses'] ) ? array_map( 'absint', $attributes['selectedCourses'] ) : array();
	$user_has_access  = false;

	if ( $learndash_active && ! empty( $selected_courses ) ) {
		foreach ( $selected_courses as $course_id ) {
			if ( sfwd_lms_has_access( $course_id, $user_id ) ) {
				$user_has_access = true;
				break;
			}
		}

		if ( ! $user_has_access ) {
			return '';
		}
	} elseif ( ! empty( $selected_courses ) && ! $learndash_active ) {
		// If LearnDash is not active but courses were selected, allow rendering.
		$user_has_access = true;
	}

	update_option( 'wp_roadmap_display_ideas_shortcode_loaded', true );

	ob_start();

	// Base taxonomies and custom taxonomies.
	$taxonomies        = array( 'idea-tag' );
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	if ( is_array( $custom_taxonomies ) ) {
		$taxonomies = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );
	}

	// Exclude status taxonomy from filters.
	$taxonomies = array_diff( $taxonomies, array( 'idea-status' ) );

	$options                    = get_option( 'wp_roadmap_settings' );
	$hide_display_ideas_heading = apply_filters( 'wp_roadmap_hide_display_ideas_heading', false );
	$new_display_ideas_heading  = apply_filters( 'wp_roadmap_custom_display_ideas_heading_text', 'Browse Ideas' );

	echo '<div class="roadmap_wrapper container mx-auto">';
	echo '<div class="browse_ideas_frontend">';

	if ( ! $hide_display_ideas_heading ) {
		echo '<h2>' . esc_html( $new_display_ideas_heading ) . '</h2>';
	}

	// Determine whether we have taxonomy terms to show filters.
	$show_filters = false;
	foreach ( $taxonomies as $taxonomy_slug ) {
		$taxonomy = get_taxonomy( $taxonomy_slug );
		if ( $taxonomy && 'idea-status' !== $taxonomy_slug ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy->name,
					'hide_empty' => false,
				)
			);
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$show_filters = true;
				break;
			}
		}
	}

	if ( $show_filters ) {
		echo '<div class="rmwp__filters-wrapper"><h4>Filters:</h4><div class="rmwp__filters-inner">';
		foreach ( $taxonomies as $taxonomy_slug ) {
			$taxonomy = get_taxonomy( $taxonomy_slug );
			if ( $taxonomy && 'idea-status' !== $taxonomy_slug ) {
				$terms = get_terms(
					array(
						'taxonomy'   => $taxonomy->name,
						'hide_empty' => false,
					)
				);
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					echo '<div class="rmwp__ideas-filter-taxonomy" data-taxonomy="' . esc_attr( $taxonomy_slug ) . '">';
					echo '<label>' . esc_html( $taxonomy->labels->singular_name ) . ':</label>';
					echo '<div class="rmwp__taxonomy-term-labels">';
					foreach ( $terms as $term ) {
						echo '<label class="rmwp__taxonomy-term-label">';
						echo '<input type="checkbox" name="idea_taxonomies[' . esc_attr( $taxonomy->name ) . '][]" value="' . esc_attr( $term->slug ) . '"> ' . esc_html( $term->name );
						echo '</label>';
					}
					echo '</div>';
					echo '<div class="rmwp__filter-match-type">';
					echo '<label><input type="radio" name="match_type_' . esc_attr( $taxonomy->name ) . '" value="any" checked> Any</label>';
					echo '<label><input type="radio" name="match_type_' . esc_attr( $taxonomy->name ) . '" value="all"> All</label>';
					echo '</div></div>';
				}
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

	$args  = array(
		'post_type'      => 'idea',
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	$query = new \WP_Query( $args );

	if ( $query->have_posts() ) {
		echo '<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$idea_id    = get_the_ID();
			$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
			$idea_class = Functions::get_idea_class_with_votes( $idea_id );
			echo '<div class="wp-roadmap-idea flex flex-col justify-between border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden ' . esc_attr( $idea_class ) . '" data-v0-t="card">';
			include plugin_dir_path( __FILE__ ) . '../../app/includes/display-ideas-grid.php';
			include plugin_dir_path( __FILE__ ) . '../../app/includes/display-ideas-admin.php';
			echo '</div>';
		}
		echo '</div></div></div>';
	} else {
		echo '<p>No ideas found.</p>';
	}

	wp_reset_postdata();

	return ob_get_clean();
}
