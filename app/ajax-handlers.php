<?php
/**
 * Ajax handling for voting and idea filtering functionality.
 */

namespace RoadMapWP\Pro\Ajax;

use RoadMapWP\Pro\Admin\Functions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clear all filter result caches.
 * This is needed when idea data changes (e.g., status updates) to ensure fresh results.
 */
function clear_filter_caches(): void {
    global $wpdb;
    
    // Delete all transients that start with 'rmwp_filter_'
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like( '_transient_rmwp_filter_' ) . '%'
        )
    );
    
    // Also delete the timeout transients
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like( '_transient_timeout_rmwp_filter_' ) . '%'
        )
    );
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'RoadMapWP: Cleared all filter caches' );
    }
}

/**
 * Handles voting functionality via AJAX.
 */
function handle_vote(): void {
    check_ajax_referer( 'wp-roadmap-vote-nonce', 'nonce' );

    $post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
    $post_id = $post_id ? intval( $post_id ) : 0;
    $user_id = get_current_user_id();

    // phpcs:ignore WordPress.NamingConventions.ValidFunctionName -- class exists in this plugin
    // @phpstan-ignore-next-line -- class is defined elsewhere in the project autoload
    if ( ! \RoadMapWP\Pro\ClassVoting\VotingHandler::can_user_vote( $user_id ) ) {
        wp_send_json_error( array( 'message' => 'You are not allowed to vote.' ) );
    }

    // Generate a unique key for non-logged-in user
    $remote_addr     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $http_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
    $user_key        = $user_id ? 'user_' . $user_id : 'guest_' . md5( $remote_addr . $http_user_agent );

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
}

add_action( 'wp_ajax_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );
add_action( 'wp_ajax_nopriv_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );


/**
 * Handles AJAX requests for filtering ideas.
 * Uses per-taxonomy object id intersection + pagination + transient caching to avoid expensive tax_query joins.
 */
function filter_ideas(): void {
    // Verify nonce and sanitize inputs
    $nonce_val = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
    if ( ! $nonce_val || ! wp_verify_nonce( $nonce_val, 'wp-roadmap-idea-filter-nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'roadmapwp-pro' ) ) );
    }

    $filter_data = filter_input( INPUT_POST, 'filter_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
    $filter_data = is_array( $filter_data ) ? $filter_data : array();
    
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'RoadMapWP Filter Debug: Received filter_data: ' . print_r( $filter_data, true ) );
    }
    $search_term = filter_input( INPUT_POST, 'search_term', FILTER_SANITIZE_STRING );
    $search_term = $search_term ? sanitize_text_field( $search_term ) : '';
    $page        = filter_input( INPUT_POST, 'page', FILTER_VALIDATE_INT );
    $page        = $page && $page > 0 ? $page : 1;
    $per_page    = filter_input( INPUT_POST, 'per_page', FILTER_VALIDATE_INT );
    $per_page    = $per_page && $per_page > 0 ? $per_page : 20;
    $cache_ttl   = 300; // seconds

    $custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
    $taxonomies        = array_merge( array( 'idea-tag' ), array_keys( $custom_taxonomies ) );

    // Build a cache key for this filter+search+page
    $cache_key = 'rmwp_filter_' . md5( wp_json_encode( array( 'filter' => $filter_data, 'search' => $search_term, 'page' => $page, 'per_page' => $per_page ) ) );
    $cached = get_transient( $cache_key );
    if ( $cached ) {
        wp_send_json_success( array( 'html' => $cached ) );
    }

    // If there are no taxonomy filters, fall back to a regular paged WP_Query
    $has_filters = false;
    $post_id_sets = array();
    foreach ( $filter_data as $taxonomy => $data ) {
        $taxonomy = sanitize_key( $taxonomy );
        if ( ! taxonomy_exists( $taxonomy ) ) {
            continue;
        }

        if ( empty( $data['terms'] ) || ! is_array( $data['terms'] ) ) {
            continue;
        }

        $has_filters = true;

        $sanitized_terms = array_map( 'sanitize_text_field', wp_unslash( $data['terms'] ) );

        // Convert slugs to term IDs
        $term_ids = array();
        foreach ( $sanitized_terms as $slug ) {
            if ( is_numeric( $slug ) ) {
                $term_ids[] = intval( $slug );
            } else {
                $term = get_term_by( 'slug', $slug, $taxonomy );
                if ( $term && ! is_wp_error( $term ) ) {
                    $term_ids[] = $term->term_id;
                }
            }
        }

        if ( empty( $term_ids ) ) {
            // No matching terms -> no posts
            wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found.', 'roadmapwp-pro' ) . '</p>' ) );
        }

        // If matchType is 'all' we need posts that have all of the term IDs in this taxonomy
        $operator = isset( $data['matchType'] ) && $data['matchType'] === 'all' ? 'ALL' : 'IN';

        if ( 'ALL' === $operator ) {
            $per_term_sets = array();
            foreach ( $term_ids as $tid ) {
                $objs = get_objects_in_term( $tid, $taxonomy );
                if ( is_wp_error( $objs ) ) {
                    $objs = array();
                }
                $per_term_sets[] = $objs;
            }
            if ( count( $per_term_sets ) > 1 ) {
                $taxonomy_post_ids = array_intersect( ...$per_term_sets );
            } else {
                $taxonomy_post_ids = reset( $per_term_sets );
            }
        } else {
            $objs = get_objects_in_term( $term_ids, $taxonomy );
            if ( is_wp_error( $objs ) ) {
                $objs = array();
            }
            $taxonomy_post_ids = $objs;
        }

        $taxonomy_post_ids = array_map( 'intval', (array) $taxonomy_post_ids );
        $post_id_sets[] = $taxonomy_post_ids;
    }

    if ( ! $has_filters ) {
        $args = array(
            'post_type'      => 'idea',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            's'              => $search_term,
            'post_status'    => 'publish',
        );

        $query = new \WP_Query( $args );
    } else {
        if ( empty( $post_id_sets ) ) {
            wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found.', 'roadmapwp-pro' ) . '</p>' ) );
        }

        // AND across taxonomies: intersect all sets
        $matching_ids = count( $post_id_sets ) > 1 ? array_intersect( ...$post_id_sets ) : reset( $post_id_sets );
        $matching_ids = array_values( array_map( 'intval', (array) $matching_ids ) );

        if ( empty( $matching_ids ) ) {
            wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found.', 'roadmapwp-pro' ) . '</p>' ) );
        }

        // Pagination via slicing the matching IDs
        $total = count( $matching_ids );
        $offset = ( $page - 1 ) * $per_page;
        $paged_ids = array_slice( $matching_ids, $offset, $per_page );

        if ( empty( $paged_ids ) ) {
            wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found.', 'roadmapwp-pro' ) . '</p>' ) );
        }

        $args = array(
            'post_type'      => 'idea',
            'post__in'       => $paged_ids,
            'orderby'        => 'post__in',
            'posts_per_page' => $per_page,
            's'              => $search_term,
            'post_status'    => 'publish',
        );

        $query = new \WP_Query( $args );
    }

    ob_start();

    if ( $query->have_posts() ) :
        echo '<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">';
        while ( $query->have_posts() ) :
            $query->the_post();
            $idea_id = (int) get_the_ID();

            // Retrieve the correct vote count for each idea
            $vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
            // @phpstan-ignore-next-line -- helper defined in admin functions within this project
            $idea_class = Functions\get_idea_class_with_votes( $idea_id );

            echo '<div class="wp-roadmap-idea flex flex-col justify-between border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden ' . esc_attr( $idea_class ) . '" data-v0-t="card">';
            include plugin_dir_path( __FILE__ ) . 'includes/display-ideas-grid.php';
            include plugin_dir_path( __FILE__ ) . 'includes/display-ideas-admin.php';
            echo '</div>';
        endwhile;
        echo '</div>';
    else :
        echo '<p>' . esc_html__( 'No ideas found.', 'roadmapwp-pro' ) . '</p>';
    endif;

    wp_reset_postdata();

    $html = ob_get_clean();
    // Cache the rendered HTML for a short time
    if ( isset( $cache_key ) ) {
        set_transient( $cache_key, $html, $cache_ttl );
    }

    wp_send_json_success( array( 'html' => $html ) );
}

add_action( 'wp_ajax_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );
add_action( 'wp_ajax_nopriv_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );


/**
 * Handles the AJAX request for deleting a custom taxonomy.
 */
function handle_delete_custom_taxonomy(): void {
    // Sanitize and validate nonce and taxonomy parameters
    $nonce_val = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
    $taxonomy  = filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_STRING );

    if ( ! $nonce_val || ! $taxonomy ) {
        wp_send_json_error( array( 'message' => __( 'Missing parameters.', 'roadmapwp-pro' ) ) );
    }

    $taxonomy = sanitize_key( $taxonomy );

    // Verify the nonce
    if ( ! wp_verify_nonce( $nonce_val, 'wp_roadmap_delete_taxonomy_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'roadmapwp-pro' ) ) );
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
function handle_delete_selected_terms(): void {
    check_ajax_referer( 'wp_roadmap_delete_terms_nonce', 'nonce' );

    $taxonomy = filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_STRING );
    $taxonomy = $taxonomy ? sanitize_key( $taxonomy ) : '';
    $terms_raw = filter_input( INPUT_POST, 'terms', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
    $terms     = is_array( $terms_raw ) ? array_map( 'intval', $terms_raw ) : array();
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
}
add_action( 'wp_ajax_delete_selected_terms', __NAMESPACE__ . '\\handle_delete_selected_terms' );


/**
 * Updates idea status via AJAX.
 */
function update_idea_status(): void {
    if ( ! check_ajax_referer( 'wp-roadmap-admin-frontend-nonce', 'nonce', false ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'RoadMapWP Pro: Failed nonce check in update_idea_status' );
        }
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'roadmapwp-pro' ) ) );
    }

    if ( ! current_user_can( 'edit_posts' ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'RoadMapWP Pro: User lacks edit_posts capability in update_idea_status' );
        }
        wp_send_json_error( array( 'message' => __( 'You do not have permission to edit ideas.', 'roadmapwp-pro' ) ) );
    }

    $idea_id  = filter_input( INPUT_POST, 'idea_id', FILTER_VALIDATE_INT );
    $statuses_raw = filter_input( INPUT_POST, 'statuses', FILTER_SANITIZE_STRING );

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'RoadMapWP Pro - Received data - idea_id: ' . print_r( $idea_id, true ) );
        error_log( 'RoadMapWP Pro - Received data - statuses_raw: ' . print_r( $statuses_raw, true ) );
    }

    if ( ! $idea_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid idea ID.', 'roadmapwp-pro' ) ) );
    }

    if ( ! $statuses_raw ) {
        wp_send_json_error( array( 'message' => __( 'No status data provided.', 'roadmapwp-pro' ) ) );
    }

    // Split the comma-separated string into an array and sanitize each value
    $statuses = array_map( 'sanitize_text_field', explode( ',', $statuses_raw ) );
    $statuses = array_filter( $statuses ); // Remove any empty values

    if ( empty( $statuses ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'RoadMapWP Pro: No valid statuses after sanitization' );
        }
        wp_send_json_error( array( 'message' => __( 'No valid status values provided.', 'roadmapwp-pro' ) ) );
    }

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'RoadMapWP Pro - Processed statuses: ' . print_r( $statuses, true ) );
    }

    $statuses = is_array( $statuses ) ? array_map( 'sanitize_text_field', $statuses ) : array();

    if ( empty( $statuses ) ) {
        wp_send_json_error( array( 'message' => __( 'No valid status values provided.', 'roadmapwp-pro' ) ) );
    }

    // Remove all existing status terms from the post
    $current_terms = wp_get_post_terms( $idea_id, 'idea-status', array( 'fields' => 'ids' ) );
    if ( is_wp_error( $current_terms ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'RoadMapWP Pro: Error getting current terms: ' . $current_terms->get_error_message() );
        }
        $current_terms = array();
    }
    
    foreach ( $current_terms as $term_id ) {
        $remove_result = wp_remove_object_terms( $idea_id, $term_id, 'idea-status' );
        if ( is_wp_error( $remove_result ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'RoadMapWP Pro: Error removing term ' . $term_id . ': ' . $remove_result->get_error_message() );
        }
    }

    $added_terms = array();
    foreach ( $statuses as $status_slug ) {
        $term = get_term_by( 'slug', $status_slug, 'idea-status' );
        if ( $term && ! is_wp_error( $term ) ) {
            $result = wp_add_object_terms( $idea_id, $term->term_id, 'idea-status' );
            if ( is_wp_error( $result ) ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'RoadMapWP Pro: Error adding term ' . $status_slug . ': ' . $result->get_error_message() );
                }
            } else {
                $added_terms[] = $status_slug;
            }
        }
    }

    if ( empty( $added_terms ) ) {
        wp_send_json_error( array( 'message' => __( 'Could not set any status terms.', 'roadmapwp-pro' ) ) );
    }

    // Clear all filter caches since the status has changed
    clear_filter_caches();

    wp_send_json_success( array(
        'message' => __( 'Status updated successfully.', 'roadmapwp-pro' ),
        'updated_terms' => $added_terms
    ) );
}
add_action( 'wp_ajax_update_idea_status', __NAMESPACE__ . '\\update_idea_status' );


/**
 * Loads ideas for a given status via AJAX.
 * Rewritten to avoid heavy tax_query by using per-taxonomy object ID lookups + intersection + pagination.
 */
function load_ideas_for_status(): void {

    check_ajax_referer( 'roadmap_nonce', 'nonce' );

    $status = filter_input( INPUT_POST, 'idea-status', FILTER_SANITIZE_STRING );
    $status = $status ? sanitize_text_field( wp_unslash( $status ) ) : '';
    $selected_taxonomiesSlugs = filter_input( INPUT_POST, 'selectedTaxonomies', FILTER_DEFAULT );
    $selected_taxonomiesSlugs = $selected_taxonomiesSlugs ? explode( ',', sanitize_text_field( wp_unslash( $selected_taxonomiesSlugs ) ) ) : array();

    $page = filter_input( INPUT_POST, 'page', FILTER_VALIDATE_INT );
    $page = $page && $page > 0 ? $page : 1;
    $per_page = filter_input( INPUT_POST, 'per_page', FILTER_VALIDATE_INT );
    $per_page = $per_page && $per_page > 0 ? $per_page : 20;
    $cache_ttl = 300;

    // Start with the set of post IDs matching the status term
    if ( empty( $status ) ) {
        wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found for this status.', 'roadmapwp-pro' ) . '</p>' ) );
    }

    $status_term = get_term_by( 'slug', $status, 'idea-status' );
    if ( ! $status_term || is_wp_error( $status_term ) ) {
        wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found for this status.', 'roadmapwp-pro' ) . '</p>' ) );
    }

    $status_post_ids = get_objects_in_term( (int) $status_term->term_id, 'idea-status' );
    if ( is_wp_error( $status_post_ids ) ) {
        $status_post_ids = array();
    }
    $status_post_ids = array_map( 'intval', (array) $status_post_ids );

    $post_id_sets = array();
    $post_id_sets[] = $status_post_ids;

    $empty_taxonomy_selected = false;

    foreach ( $selected_taxonomiesSlugs as $slug ) {
        if ( empty( $slug ) ) {
            continue;
        }

        $terms = get_terms(
            array(
                'taxonomy' => sanitize_key( $slug ),
                'fields'   => 'slugs',
            )
        );

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            // Convert slugs to term IDs and fetch object IDs
            $term_ids = array();
            foreach ( $terms as $t_slug ) {
                $t = get_term_by( 'slug', $t_slug, $slug );
                if ( $t && ! is_wp_error( $t ) ) {
                    $term_ids[] = $t->term_id;
                }
            }
            if ( ! empty( $term_ids ) ) {
                $objs = get_objects_in_term( $term_ids, $slug );
                if ( is_wp_error( $objs ) ) {
                    $objs = array();
                }
                $post_id_sets[] = array_map( 'intval', (array) $objs );
            }
        } else {
            $empty_taxonomy_selected = true;
        }
    }

    if ( $empty_taxonomy_selected && count( $post_id_sets ) === 1 ) {
        wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found for the selected taxonomies.', 'roadmapwp-pro' ) . '</p>' ) );
    }

    // Intersect all sets (AND semantics)
    $matching_ids = count( $post_id_sets ) > 1 ? array_intersect( ...$post_id_sets ) : reset( $post_id_sets );
    $matching_ids = array_values( array_map( 'intval', (array) $matching_ids ) );

    if ( empty( $matching_ids ) ) {
        wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found for this status.', 'roadmapwp-pro' ) . '</p>' ) );
    }

    // Pagination and final query
    $offset = ( $page - 1 ) * $per_page;
    $paged_ids = array_slice( $matching_ids, $offset, $per_page );

    if ( empty( $paged_ids ) ) {
        wp_send_json_success( array( 'html' => '<p>' . esc_html__( 'No ideas found for this status.', 'roadmapwp-pro' ) . '</p>' ) );
    }

    $args = array(
        'post_type'      => 'idea',
        'post__in'       => $paged_ids,
        'orderby'        => 'post__in',
        'posts_per_page' => $per_page,
        'post_status'    => 'publish',
    );

    $query = new \WP_Query( $args );

    // Set up taxonomies to display (exclude idea-status)
    $custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
    $taxonomies        = array_merge( array( 'idea-tag' ), array_keys( $custom_taxonomies ) );

    ob_start();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $idea_id = (int) get_the_ID();
            $vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
            // @phpstan-ignore-next-line -- helper defined in admin functions within this project
            $idea_class = Functions\get_idea_class_with_votes( $idea_id );
            include plugin_dir_path( __FILE__ ) . 'includes/display-ideas-grid.php';
        }
    } else {
        echo '<p>' . esc_html__( 'No ideas found for this status.', 'roadmapwp-pro' ) . '</p>';
    }

    wp_reset_postdata();

    $html = ob_get_clean();
    wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_load_ideas_for_status', __NAMESPACE__ . '\\load_ideas_for_status' );
add_action( 'wp_ajax_nopriv_load_ideas_for_status', __NAMESPACE__ . '\\load_ideas_for_status' );