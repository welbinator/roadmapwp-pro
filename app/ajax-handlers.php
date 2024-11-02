<?php
/*
* Ajax handling for voting functionality.
*/

namespace RoadMapWP\Pro\Ajax;
use RoadMapWP\Free\Ajax as FreeAjax;
use RoadMapWP\Pro\Admin\Functions;

/**
 * Extends the free version's handle_vote function.
 */
function handle_vote() {
	FreeAjax\handle_vote();
}

add_action( 'wp_ajax_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );
add_action( 'wp_ajax_nopriv_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );

/**
 * Extends the free version's filter_ideas function with pro-only adjustments.
 */
function filter_ideas() {
	check_ajax_referer( 'wp-roadmap-idea-filter-nonce', 'nonce' );

	$filter_data = isset($_POST['filter_data']) ? (array) $_POST['filter_data'] : [];
	$search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
	$tax_query   = [];

	$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', [] );
	$taxonomies = array_merge( [ 'idea-tag' ], array_keys( $custom_taxonomies ) );

	foreach ($filter_data as $taxonomy => $data) {
		$taxonomy = sanitize_key($taxonomy);
		if (!taxonomy_exists($taxonomy)) {
			continue;
		}
		if (!empty($data['terms']) && is_array($data['terms'])) {
			$sanitized_terms = array_map('sanitize_text_field', $data['terms']);
			$operator = isset($data['matchType']) && $data['matchType'] === 'all' ? 'AND' : 'IN';

			$tax_query[] = [
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $sanitized_terms,
				'operator' => $operator,
			];
		}
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	$args = [
		'post_type'      => 'idea',
		'posts_per_page' => -1,
		'tax_query'      => $tax_query,
		's'              => $search_term,
		'post_status'    => 'publish',
	];

	$query = new \WP_Query( $args );

	if ( $query->have_posts() ) {
		echo '<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$idea_id = get_the_ID();
			$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
			$idea_class = Functions\get_idea_class_with_votes($idea_id);

			echo '<div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden ' . esc_attr($idea_class) . '" data-v0-t="card">';
			include plugin_dir_path( __FILE__ ) . 'includes/display-ideas-grid.php';
			echo '</div>';
		}
		echo '</div>';
	} else {
		echo '<p>No ideas found.</p>';
	}

	wp_reset_postdata();
	wp_die();
}

add_action( 'wp_ajax_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );
add_action( 'wp_ajax_nopriv_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );

/**
 * Extends the free version with status update functionality
 */
function update_idea_status() {
	check_ajax_referer( 'pro-frontend-script-nonce', 'nonce' );

	if ( ! isset( $_POST['idea_id'] ) || empty( $_POST['idea_id'] ) ) {
		wp_send_json_error( [ 'message' => 'Missing idea ID' ] );
		wp_die();
	}

	if ( ! isset( $_POST['statuses'] ) || empty( $_POST['statuses'] ) ) {
		wp_send_json_error( [ 'message' => 'No statuses provided' ] );
		wp_die();
	}

	$idea_id  = intval( $_POST['idea_id'] );
	$statuses = json_decode( stripslashes( $_POST['statuses'] ), true );

	if ( $idea_id && ! empty( $statuses ) ) {
		$current_terms = wp_get_post_terms( $idea_id, 'idea-status', [ 'fields' => 'ids' ] );
		foreach ( $current_terms as $term_id ) {
			wp_remove_object_terms( $idea_id, $term_id, 'idea-status' );
		}

		foreach ( $statuses as $status_slug ) {
			$term = get_term_by( 'slug', $status_slug, 'idea-status' );
			if ( $term && ! is_wp_error( $term ) ) {
				wp_add_object_terms( $idea_id, $term->term_id, 'idea-status' );
			}
		}

		wp_send_json_success();
	} else {
		wp_send_json_error( 'Invalid data' );
	}

	wp_die();
}

add_action( 'wp_ajax_update_idea_status', __NAMESPACE__ . '\\update_idea_status' );

/**
 * Delete Taxonomy
 **/
// Add action for AJAX request to delete a custom taxonomy
add_action('wp_ajax_delete_custom_taxonomy', __NAMESPACE__ . '\\delete_custom_taxonomy_callback');

function delete_custom_taxonomy_callback() {
    // Verify the nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_roadmap_delete_taxonomy_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed.']);
        wp_die();
    }

    // Get and sanitize the taxonomy to delete
    if (empty($_POST['taxonomy'])) {
        wp_send_json_error(['message' => 'No taxonomy specified.']);
        wp_die();
    }
    $taxonomy_slug = sanitize_text_field($_POST['taxonomy']);

    // Get the custom taxonomies from options
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());

    // Check if the taxonomy exists in the custom taxonomies and delete it
    if (array_key_exists($taxonomy_slug, $custom_taxonomies)) {
        unset($custom_taxonomies[$taxonomy_slug]);
        update_option('wp_roadmap_custom_taxonomies', $custom_taxonomies);

        // Remove the taxonomy using unregister_taxonomy if necessary
        unregister_taxonomy($taxonomy_slug);

        wp_send_json_success(['message' => 'Taxonomy deleted successfully.']);
    } else {
        wp_send_json_error(['message' => 'Taxonomy not found.']);
    }

    wp_die();
}
