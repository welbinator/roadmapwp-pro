<?php

namespace RoadMapWP\Pro\CPT;

/**
 * Register the custom post type.
 */
function register_idea_post_type() {
	$options = get_option( 'wp_roadmap_settings' );

	$supports = array( 'title', 'editor', 'author' ); // include 'comments' support.

	$labels = array(
		'name'               => _x( 'Ideas', 'post type general name', 'roadmapwp-pro' ),
		'singular_name'      => _x( 'Idea', 'post type singular name', 'roadmapwp-pro' ),
		'menu_name'          => _x( 'Ideas', 'admin menu', 'roadmapwp-pro' ),
		'name_admin_bar'     => _x( 'Idea', 'add new on admin bar', 'roadmapwp-pro' ),
		'add_new'            => _x( 'Add New', 'idea', 'roadmapwp-pro' ),
		'add_new_item'       => __( 'Add New Idea', 'roadmapwp-pro' ),
		'new_item'           => __( 'New Idea', 'roadmapwp-pro' ),
		'edit_item'          => __( 'Edit Idea', 'roadmapwp-pro' ),
		'view_item'          => __( 'View Idea', 'roadmapwp-pro' ),
		'all_items'          => __( 'All Ideas', 'roadmapwp-pro' ),
		'search_items'       => __( 'Search Ideas', 'roadmapwp-pro' ),
		'parent_item_colon'  => __( 'Parent Ideas:', 'roadmapwp-pro' ),
		'not_found'          => __( 'No ideas found.', 'roadmapwp-pro' ),
		'not_found_in_trash' => __( 'No ideas found in Trash.', 'roadmapwp-pro' ),
	);

	// Fetch all taxonomies associated with 'idea' post type.
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_keys( $custom_taxonomies );

	// Add default taxonomies if they aren't already included.
	if ( ! in_array( 'status', $taxonomies ) ) {
		$taxonomies[] = 'status';  // Default taxonomy 'status'.
	}
	if ( ! in_array( 'tag', $taxonomies ) ) {
		$taxonomies[] = 'tag';  // Default taxonomy 'tag'.
	}

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => false,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'idea' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'taxonomies'         => $taxonomies,
		'supports'           => array( 'title', 'editor', 'author', 'comments' ),
		'show_in_rest'          => true,
	);

	register_post_type( 'idea', $args );
}

add_action( 'init', __NAMESPACE__ . '\\register_idea_post_type' );


/**
 * Register default taxonomies.
 */
function register_default_idea_taxonomies() {
	// Define default taxonomies with their properties.
	$default_taxonomies = array(
		'status'   => array(
			'singular' => __( 'Status', 'roadmapwp-pro' ), // Translatable.
			'plural'   => __( 'Status', 'roadmapwp-pro' ),   // Translatable.
			'public'   => true,  // Make status taxonomy private.
		),
		'idea-tag' => array(
			'singular' => __( 'Tag', 'roadmapwp-pro' ),    // Translatable.
			'plural'   => __( 'Tags', 'roadmapwp-pro' ),     // Translatable.
			'public'   => true,  // Keep tag taxonomy public.
		),
	);

	foreach ( $default_taxonomies as $slug => $properties ) {
		if ( ! taxonomy_exists( $slug ) ) {
			register_taxonomy(
				$slug,
				'idea',
				array(
					'label'             => $properties['plural'],
					'labels'            => array(
						'name'          => $properties['plural'],
						'singular_name' => $properties['singular'],
						// ... other labels ...
					),
					'public'            => $properties['public'],
					'hierarchical'      => ( $slug == 'status' ),
					'show_ui'           => true,
					'show_in_rest'      => true,
					'show_admin_column' => true,
				)
			);
		}
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_default_idea_taxonomies' );

/**
 * Automatically assign "New Idea" status to new idea posts.
 *
 * @param int      $post_id The post ID.
 * @param \WP_Post $post The post object.
 * @param bool     $update Whether this is an existing post being updated.
 */
function auto_assign_new_idea_status( $post_id, $post, $update ) {
	// If this is an update, not a new post, or if it's an autosave, don't do anything.
	if ( $update || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Check if the term exists.
	$term = term_exists( 'New Idea', 'status' );

	// If the term doesn't exist, add it.
	if ( $term === 0 || $term === null ) {
		$term = wp_insert_term( 'New Idea', 'status' );
	}

	// Check for errors.
	if ( is_wp_error( $term ) ) {
		error_log( 'Error auto-assigning "New Idea" status: ' . $term->get_error_message() );
		return;
	}

	// Assign "New Idea" status to this idea post using the term slug.
	wp_set_object_terms( $post_id, 'new-idea', 'status' );
}

// add_action('save_post_idea', 'wp_roadmap_pro_auto_assign_new_idea_status', 10, 3);.

/**
 * Register custom taxonomies.
 */
function register_custom_idea_taxonomies() {
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	foreach ( $custom_taxonomies as $taxonomy_slug => $taxonomy_data ) {
		if ( ! taxonomy_exists( $taxonomy_slug ) ) {
			register_taxonomy( $taxonomy_slug, 'idea', $taxonomy_data );
		}
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_custom_idea_taxonomies', 0 );
