<?php
/**
 * This file handles the custom taxonomy management in the Pro version of the RoadMapWP plugin.
 *
 * @package RoadMapWP\Pro\Settings\Taxonomies
 */

namespace RoadMapWP\Pro\Settings\Taxonomies;

/**
 * Outputs the HTML content for custom taxonomy management.
 *
 * @return string The HTML output for custom taxonomy management.
 */
function custom_taxonomy_content() {
	ob_start();

	// Flag to trigger JavaScript redirection
	$should_redirect = false;

	// Fetch custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	// Handle taxonomy deletion
	if ( isset( $_GET['action'], $_GET['taxonomy'], $_GET['_wpnonce'] ) && $_GET['action'] == 'delete' ) {
		if ( wp_verify_nonce( $_GET['_wpnonce'], 'delete_taxonomy_' . $_GET['taxonomy'] ) && array_key_exists( $_GET['taxonomy'], $custom_taxonomies ) ) {
			unset( $custom_taxonomies[ $_GET['taxonomy'] ] );
			update_option( 'wp_roadmap_custom_taxonomies', $custom_taxonomies );
			$should_redirect = true;
		}
	}

	// Check if a new term is being added
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['new_term'] ) && ! empty( $_POST['taxonomy_slug'] ) ) {
		// Verify the nonce for security
		if ( ! isset( $_POST['wp_roadmap_add_term_nonce'] ) || ! wp_verify_nonce( $_POST['wp_roadmap_add_term_nonce'], 'add_term_to_' . $_POST['taxonomy_slug'] ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'roadmapwp-pro' ) );
		}

		$new_term      = sanitize_text_field( $_POST['new_term'] );
		$taxonomy_slug = sanitize_text_field( $_POST['taxonomy_slug'] );

		// Check if the term already exists in the specified taxonomy
		if ( term_exists( $new_term, $taxonomy_slug ) ) {
			// Term already exists
			echo '<div class="error"><p>Term already exists in this taxonomy.</p></div>';
		} else {
			$inserted_term = wp_insert_term( $new_term, $taxonomy_slug );
			if ( is_wp_error( $inserted_term ) ) {
				// Handle error: Term could not be added
				echo '<div class="error"><p>Term could not be added: ' . esc_html( $inserted_term->get_error_message() ) . '</p></div>';
			} else {
				// Term added successfully
				echo '<div class="updated"><p>Term added successfully.</p></div>';
			}
		}
	}

	// Check if the form has been submitted for adding a new taxonomy
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['wp_roadmap_pro_nonce'], $_POST['taxonomy_slug'] ) ) {
		if ( wp_verify_nonce( $_POST['wp_roadmap_pro_nonce'], 'wp_roadmap_pro_add_taxonomy' ) ) {
			$taxonomy_slug     = sanitize_key( $_POST['taxonomy_slug'] );
			$taxonomy_singular = sanitize_text_field( $_POST['taxonomy_singular'] );
			$taxonomy_plural   = sanitize_text_field( $_POST['taxonomy_plural'] );

			$labels = array(
				'name'          => $taxonomy_plural,
				'singular_name' => $taxonomy_singular,
			);

			$taxonomy_data = array(
				'labels'            => $labels,
				'public'            => true,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $taxonomy_slug ),
			);

			register_taxonomy( $taxonomy_slug, 'idea', $taxonomy_data );

			$custom_taxonomies                   = get_option( 'wp_roadmap_custom_taxonomies', array() );
			$custom_taxonomies[ $taxonomy_slug ] = $taxonomy_data;
			update_option( 'wp_roadmap_custom_taxonomies', $custom_taxonomies );

			$should_redirect = true;
		}
	}

	echo '<div class="wrap custom">';
	echo '<h2>Add Custom Taxonomy</h2>';
	echo '<form action="" method="post">';
	wp_nonce_field( 'wp_roadmap_pro_add_taxonomy', 'wp_roadmap_pro_nonce' );
	echo '<ul class="flex-outer">';
	echo '<li class="new_taxonomy_form_input">';
	echo '<label for="taxonomy_slug">Slug:</label>';
	echo '<input type="text" id="taxonomy_slug" name="taxonomy_slug" required>';
	echo '</li>';
	echo '<li class="new_taxonomy_form_input">';
	echo '<label for="taxonomy_singular">Singular Name:</label>';
	echo '<input type="text" id="taxonomy_singular" name="taxonomy_singular" required>';
	echo '</li>';
	echo '<li class="new_taxonomy_form_input">';
	echo '<label for="taxonomy_plural">Plural Name:</label>';
	echo '<input type="text" id="taxonomy_plural" name="taxonomy_plural" required>';
	echo '</li>';
	echo '<li class="new_taxonomy_form_input">';
	echo '<input type="submit" value="Add Taxonomy">';
	echo '</li>';
	echo '</ul>';
	echo '</form>';
	echo '<hr style="margin:20px; border:2px solid #8080802e;" />';

	echo '<h2>Manage Taxonomies</h2>';
	// Display existing taxonomies and their terms, including 'status' and 'idea-tag'
	$taxonomies = get_taxonomies( array( 'object_type' => array( 'idea' ) ), 'objects' );
	foreach ( $taxonomies as $taxonomy ) {
		// Exclude custom taxonomies from this loop
		if ( ! array_key_exists( $taxonomy->name, $custom_taxonomies ) ) {
			echo '<h4>Taxonomy name: <strong>' . esc_html( $taxonomy->labels->name ) . '</strong></h4>';
			echo '<h5 style="margin-bottom: 0;">Terms:</h5>';
			$idea_terms = get_terms(
				array(
					'taxonomy'   => $taxonomy->name,
					'hide_empty' => false,
				)
			);
			if ( ! empty( $idea_terms ) && ! is_wp_error( $idea_terms ) ) {
				echo '<form method="post" class="delete-terms-form" data-taxonomy="' . esc_attr( $taxonomy->name ) . '">';
				echo '<ul class="terms-list">';
				foreach ( $idea_terms as $idea_term ) {
					echo '<li>';
					echo '<input type="checkbox" name="terms[]" value="' . esc_attr( $idea_term->term_id ) . '"> ' . esc_html( $idea_term->name );
					echo '</li>';
				}
				echo '</ul>';
				echo '<input type="submit" value="Delete Selected Terms" class="button delete-terms-button">';
				echo '</form>';
			} else {
				echo '<p>No terms found for ' . esc_html( $taxonomy->labels->name ) . '.</p>';
			}

			echo '<form action="' . esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ) . '" method="post">';
			echo '<input type="text" name="new_term" placeholder="New Term for ' . esc_attr( $taxonomy->labels->singular_name ) . '" />';
			echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr( $taxonomy->name ) . '" />';
			echo '<input type="submit" value="Add Term" />';
			echo wp_nonce_field( 'add_term_to_' . $taxonomy->name, 'wp_roadmap_add_term_nonce' );
			echo '</form>';
			echo '<hr style="margin:20px; border:2px solid #8080802e;" />';
		}
	}

	// Display and provide deletion option for custom taxonomies
	foreach ( $custom_taxonomies as $taxonomy_slug => $taxonomy_data ) {
		echo '<h3>' . esc_html( $taxonomy_data['labels']['name'] ) . '</h3>';
		echo '<a href="#" class="delete-taxonomy" data-taxonomy="' . esc_attr( $taxonomy_slug ) . '">Delete this taxonomy</a>';

		// Display and delete terms for custom taxonomies
		$idea_terms = get_terms(
			array(
				'taxonomy'   => $taxonomy_slug,
				'hide_empty' => false,
			)
		);
		if ( ! empty( $idea_terms ) && ! is_wp_error( $idea_terms ) ) {
			echo '<form method="post" class="delete-terms-form" data-taxonomy="' . esc_attr( $taxonomy_slug ) . '">';
			echo '<ul class="terms-list">';
			foreach ( $idea_terms as $idea_term ) {
				echo '<li>';
				echo '<input type="checkbox" name="terms[]" value="' . esc_attr( $idea_term->term_id ) . '"> ' . esc_html( $idea_term->name );
				echo '</li>';
			}
			echo '</ul>';
			echo '<input type="submit" value="Delete Selected Terms" class="button delete-terms-button">';
			echo '</form>';
		} else {
			echo '<p>No terms found for ' . esc_html( $taxonomy_data['labels']['name'] ) . '.</p>';
		}

		// Form to add a new term to this custom taxonomy
		echo '<form action="' . esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ) . '" method="post">';
		echo '<input type="text" name="new_term" placeholder="New Term for ' . esc_attr( $taxonomy_data['labels']['singular_name'] ) . '" />';
		echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr( $taxonomy_slug ) . '" />';
		echo '<input type="submit" value="Add Term" />';
		echo wp_nonce_field( 'add_term_to_' . $taxonomy_slug, 'wp_roadmap_add_term_nonce' );
		echo '</form>';

		echo '<hr style="margin:20px; border:2px solid #8080802e;" />';
	}

	if ( $should_redirect ) {
		echo '<script type="text/javascript">';
		echo 'window.location.href = "' . esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ) . '";';
		echo '</script>';
	}

	echo '</div>';

	return ob_get_clean();
}

add_filter( 'wp_roadmap_pro_add_taxonomy_feature', __NAMESPACE__ . '\\custom_taxonomy_content' );
