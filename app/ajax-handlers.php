<?php
/*
* Ajax handling for voting functionality.
*/

namespace RoadMapWP\Pro\Ajax;
use RoadMapWP\Pro\Admin\Functions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

/**
 * Handles voting functionality via AJAX.
 */
function handle_vote() {
	check_ajax_referer( 'wp-roadmap-vote-nonce', 'nonce' );

	$post_id = intval( $_POST['post_id'] );
	$user_id = get_current_user_id();

	// Check if the user is allowed to vote
    if (!\RoadMapWP\Pro\ClassVoting\VotingHandler::can_user_vote($user_id)) {
        wp_send_json_error(['message' => 'You are not allowed to vote.']);
        wp_die();
    }

	// Generate a unique key for non-logged-in user
	$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
	$http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
	$user_key = $user_id ? 'user_' . $user_id : 'guest_' . md5($remote_addr . $http_user_agent);


	// Retrieve the current vote count
	$current_votes = get_post_meta( $post_id, 'idea_votes', true ) ?: 0;

	// Check if this user or guest has already voted
	$has_voted = get_post_meta( $post_id, 'voted_' . $user_key, true );

	if ( $has_voted ) {
		// User or guest has voted, remove their vote
		$new_votes = max( $current_votes - 1, 0 );
		delete_post_meta( $post_id, 'voted_' . $user_key );
	} else {
		// User or guest hasn't voted, add their vote
		$new_votes = $current_votes + 1;
		update_post_meta( $post_id, 'voted_' . $user_key, true );
	}

	// Update the post meta with the new vote count
	update_post_meta( $post_id, 'idea_votes', $new_votes );

	wp_send_json_success(
		array(
			'new_count' => $new_votes,
			'voted'     => ! $has_voted,
		)
	);

	wp_die();
}

add_action( 'wp_ajax_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );
add_action( 'wp_ajax_nopriv_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );

/**
 * Handles AJAX requests for filtering ideas.
 */
function filter_ideas() {
	check_ajax_referer( 'wp-roadmap-idea-filter-nonce', 'nonce' );

	$filter_data = isset($_POST['filter_data']) ? (array) $_POST['filter_data'] : array();
	$search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
	$tax_query   = array();

	$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies = array_merge( array( 'idea-tag' ), array_keys( $custom_taxonomies ) );
	
	foreach ($filter_data as $taxonomy => $data) {
		// Sanitize taxonomy to ensure it's a valid taxonomy name
		$taxonomy = sanitize_key($taxonomy);
		if (!taxonomy_exists($taxonomy)) {
			continue; // Skip this iteration if the taxonomy is not valid
		}
	
		// Validate and sanitize 'terms' if they are set and is an array
		if (!empty($data['terms']) && is_array($data['terms'])) {
			$sanitized_terms = array_map('sanitize_text_field', $data['terms']);
			$operator = isset($data['matchType']) && $data['matchType'] === 'all' ? 'AND' : 'IN';
			
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $sanitized_terms,
				'operator' => $operator,
			);
		}
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}
	$args = array(
        'post_type'      => 'idea',
        'posts_per_page' => -1,
        'tax_query'      => $tax_query,
        's'              => $search_term,
		'post_status'    => 'publish',
    );
	

	$query = new \WP_Query( $args );
	
	if ( $query->have_posts() ) : ?>
		<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$idea_id = get_the_ID();

				// Retrieve the correct vote count for each idea
				$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
                $idea_class = Functions\get_idea_class_with_votes($idea_id);
				
				?>
	
				<div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden <?php echo esc_attr($idea_class); ?>" data-v0-t="card">
					<?php include plugin_dir_path( __FILE__ ) . 'includes/display-ideas-grid.php'; ?>
				</div>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<p><?php esc_html_e( 'No ideas found.', 'roadmapwp-pro' ); ?></p>
		<?php
	endif;

	wp_reset_postdata();
	wp_die();
}


add_action( 'wp_ajax_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );
add_action( 'wp_ajax_nopriv_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );



/**
 * Handles the AJAX request for deleting a custom taxonomy.
 */
function handle_delete_custom_taxonomy() {
	// Check if the nonce and taxonomy parameters are set
	if ( ! isset( $_POST['nonce'], $_POST['taxonomy'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Missing parameters.', 'roadmapwp-pro' ) ) );
		return;
	}

	// Sanitize and assign the taxonomy
	$taxonomy = sanitize_text_field( $_POST['taxonomy'] );

	// Verify the nonce
	if ( ! wp_verify_nonce( $_POST['nonce'], 'wp_roadmap_delete_taxonomy_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'roadmapwp-pro' ) ) );
		return;
	}

	// Fetch the custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	// Check if the taxonomy exists and delete it
	if ( array_key_exists( $taxonomy, $custom_taxonomies ) ) {
		unset( $custom_taxonomies[ $taxonomy ] );
		update_option( 'wp_roadmap_custom_taxonomies', $custom_taxonomies );
		wp_send_json_success();
	} else {
		wp_send_json_error( array( 'message' => __( 'Taxonomy not found.', 'roadmapwp-pro' ) ) );
	}
}
add_action( 'wp_ajax_delete_custom_taxonomy', __NAMESPACE__ . '\\handle_delete_custom_taxonomy' );


/**
 * Handles the AJAX request for deleting selected terms.
 */
function handle_delete_selected_terms() {
	check_ajax_referer( 'wp_roadmap_delete_terms_nonce', 'nonce' );

	$taxonomy            = sanitize_text_field( $_POST['taxonomy'] );
	$terms               = array_map( 'intval', (array) $_POST['terms'] );
	$deletion_successful = true;

	foreach ( $terms as $term_id ) {
		$deleted_term = wp_delete_term( $term_id, $taxonomy );
		if ( is_wp_error( $deleted_term ) ) {
			$deletion_successful = false;
			break; // Exit the loop if any deletion fails
		}
	}

	if ( $deletion_successful ) {
		wp_send_json_success( array( 'message' => 'Term deleted successfully.' ) );
	} else {
		wp_send_json_error( array( 'message' => 'Error occurred while deleting term.' ) );
	}

	wp_die(); // This is important to terminate immediately and return a proper response
}
add_action( 'wp_ajax_delete_selected_terms', __NAMESPACE__ . '\\handle_delete_selected_terms' );


/**
 * Updates idea status via AJAX.
 */
function update_idea_status() {
	check_ajax_referer( 'wp-roadmap-admin-frontend-nonce', 'nonce' );

	$idea_id  = isset( $_POST['idea_id'] ) ? intval( $_POST['idea_id'] ) : 0;
	$statuses = isset( $_POST['statuses'] ) ? json_decode( stripslashes( $_POST['statuses'] ), true ) : array();

	if ( $idea_id && ! empty( $statuses ) ) {
		// Remove all existing status terms from the post
		$current_terms = wp_get_post_terms( $idea_id, 'idea-status', array( 'fields' => 'ids' ) );
		foreach ( $current_terms as $term_id ) {
			wp_remove_object_terms( $idea_id, $term_id, 'idea-status' );
		}

		// Add each new status term
		foreach ( $statuses as $status_slug ) {
			$term = get_term_by( 'slug', $status_slug, 'idea-status' );
			if ( $term && ! is_wp_error( $term ) ) {
				wp_add_object_terms( $idea_id, $term->term_id, 'idea-status' );
			}
		}

		// Check current terms after setting
		$current_terms = wp_get_post_terms( $idea_id, 'idea-status', array( 'fields' => 'slugs' ) );

		wp_send_json_success();
	} else {
		wp_send_json_error( 'Invalid data' );
	}
}
add_action( 'wp_ajax_update_idea_status', __NAMESPACE__ . '\\update_idea_status' );


/**
 * Loads ideas for a given status via AJAX.
 */
function load_ideas_for_status() {

	check_ajax_referer( 'roadmap_nonce', 'nonce' );

	$status                  = isset( $_POST['idea-status'] ) ? sanitize_text_field( $_POST['idea-status'] ) : '';
	$selected_taxonomiesSlugs = isset( $_POST['selectedTaxonomies'] ) ? explode( ',', sanitize_text_field( $_POST['selectedTaxonomies'] ) ) : array();

	// Initialize the tax query with the status term
	$tax_query = array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'idea-status',
			'field'    => 'slug',
			'terms'    => $status,
		),
	);

	$taxonomy_queries        = array();
	$empty_taxonomy_selected = false;

	// Modify the tax query if selected taxonomies are provided
	foreach ( $selected_taxonomiesSlugs as $slug ) {
		if ( ! empty( $slug ) ) {
			$terms = get_terms(
				array(
					'taxonomy' => $slug,
					'fields'   => 'slugs',
				)
			);
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$taxonomy_queries[] = array(
					'taxonomy' => $slug,
					'field'    => 'slug',
					'terms'    => $terms,
					'operator' => 'IN',
				);
			} else {
				$empty_taxonomy_selected = true;
			}
		}
	}

	if ( ! empty( $taxonomy_queries ) ) {
		$tax_query[] = array_merge( array( 'relation' => 'OR' ), $taxonomy_queries );
	}

	if ( $empty_taxonomy_selected && count( $taxonomy_queries ) === 0 ) {
		wp_send_json_success( array( 'html' => '<p>No ideas found for the selected taxonomies.</p>' ) );
		return;
	}

	$args = array(
		'post_type'      => 'idea',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'tax_query'      => $tax_query,
	);

	$query = new \WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$idea_id = get_the_ID();
			// Retrieve all taxonomies associated with the 'idea' post type, excluding 'idea-status'
			$idea_taxonomies     = get_object_taxonomies( 'idea', 'names' );
			
			$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', array() );
			$taxonomies = array_merge( array( 'idea-tag' ), array_keys( $custom_taxonomies ) );

			$idea_class = Functions\get_idea_class_with_votes($idea_id);

			$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
			?>
			<div class="wut wp-roadmap-idea rounded-lg border bg-card text-card-foreground shadow-lg <?php echo esc_attr($idea_class); ?>" data-v0-t="card">
				<?php include plugin_dir_path(__FILE__) . 'includes/display-ideas-grid.php'; ?>
				
			</div>

			<?php
		}
	} else {
		echo '<p>No ideas found for this status.</p>';
	}

	wp_reset_postdata();

	$html = ob_get_clean();
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_load_ideas_for_status', __NAMESPACE__ . '\\load_ideas_for_status' );
add_action( 'wp_ajax_nopriv_load_ideas_for_status', __NAMESPACE__ . '\\load_ideas_for_status' );





