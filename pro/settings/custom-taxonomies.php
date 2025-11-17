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

	// Restrict access to administrators.
	if ( ! current_user_can( 'manage_options' ) ) {
		return '<div class="error"><p>' . esc_html__( 'You do not have sufficient permissions to access this page.', 'roadmapwp-pro' ) . '</p></div>';
	}

	// Flag to trigger JavaScript redirection.
	$should_redirect = false;
	$error_message   = '';

	// Fetch custom taxonomies.
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	// Handle taxonomy deletion.
	if ( isset( $_GET['action'], $_GET['taxonomy'], $_GET['_wpnonce'] ) ) {
		$action_param   = sanitize_key( wp_unslash( $_GET['action'] ) );
		$taxonomy_param = sanitize_key( wp_unslash( $_GET['taxonomy'] ) );
		$nonce_param    = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

		if ( 'delete' === $action_param && current_user_can( 'manage_options' ) ) {
			if ( wp_verify_nonce( $nonce_param, 'delete_taxonomy_' . $taxonomy_param ) && array_key_exists( $taxonomy_param, $custom_taxonomies ) ) {
				unset( $custom_taxonomies[ $taxonomy_param ] );
				update_option( 'wp_roadmap_custom_taxonomies', $custom_taxonomies );
				$should_redirect = true;
			}
		}
	}

	// Check if a new term is being added.
	if ( filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) === 'POST' && ! empty( $_POST['new_term'] ) && ! empty( $_POST['taxonomy_slug'] ) ) {
				// Verify the nonce for security.
		$nonce             = isset( $_POST['wp_roadmap_add_term_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_roadmap_add_term_nonce'] ) ) : '';
		$taxonomy_slug_raw = sanitize_text_field( wp_unslash( $_POST['taxonomy_slug'] ) );

		if ( ! wp_verify_nonce( $nonce, 'add_term_to_' . $taxonomy_slug_raw ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'roadmapwp-pro' ) );
		}

		$new_term      = sanitize_text_field( wp_unslash( $_POST['new_term'] ) );
		$taxonomy_slug = sanitize_key( $taxonomy_slug_raw );

		// Check if the term already exists in the specified taxonomy.
		if ( term_exists( $new_term, $taxonomy_slug ) ) {
			// Term already exists.
			echo '<div class="error"><p>' . esc_html__( 'Term already exists in this taxonomy.', 'roadmapwp-pro' ) . '</p></div>';
		} else {
			$inserted_term = wp_insert_term( $new_term, $taxonomy_slug );
			if ( is_wp_error( $inserted_term ) ) {
				// Handle error: Term could not be added.
				echo '<div class="error"><p>' . esc_html__( 'Term could not be added:', 'roadmapwp-pro' ) . ' ' . esc_html( $inserted_term->get_error_message() ) . '</p></div>';
			} else {
				// Term added successfully.
				echo '<div class="updated"><p>' . esc_html__( 'Term added successfully.', 'roadmapwp-pro' ) . '</p></div>';
			}
		}
	}

	// Check if the form has been submitted for adding a new taxonomy.
	if ( filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) === 'POST' && isset( $_POST['wp_roadmap_pro_nonce'], $_POST['taxonomy_slug'] ) && empty( $_POST['new_term'] ) ) {
		$nonce = isset( $_POST['wp_roadmap_pro_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_roadmap_pro_nonce'] ) ) : '';
		if ( wp_verify_nonce( $nonce, 'wp_roadmap_pro_add_taxonomy' ) && current_user_can( 'manage_options' ) ) {
			$taxonomy_slug     = sanitize_key( wp_unslash( $_POST['taxonomy_slug'] ) );
			$taxonomy_singular = sanitize_text_field( wp_unslash( $_POST['taxonomy_singular'] ?? '' ) );
			$taxonomy_plural   = sanitize_text_field( wp_unslash( $_POST['taxonomy_plural'] ?? '' ) );

			// Check if the slug is "type".
			if ( 'type' === $taxonomy_slug ) {
				$error_message = esc_html__( "'type' is a reserved term and cannot be used as the slug for a custom taxonomy", 'roadmapwp-pro' );
			} else {
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

				$custom_taxonomies[ $taxonomy_slug ] = $taxonomy_data;
				update_option( 'wp_roadmap_custom_taxonomies', $custom_taxonomies );

				flush_rewrite_rules();

				$should_redirect = true;
			}
		}
	}

	if ( ! empty( $error_message ) ) {
		echo '<div class="error"><p>' . esc_html( $error_message ) . '</p></div>';
	}

	echo '<div class="wrap custom">';
	echo '<h2>' . esc_html__( 'Add Custom Taxonomy', 'roadmapwp-pro' ) . '</h2>';
	echo '<form action="" method="post">';
	wp_nonce_field( 'wp_roadmap_pro_add_taxonomy', 'wp_roadmap_pro_nonce' );
	echo '<ul class="rmwp__flex-outer">';
	echo '<li class="rmwp__new_taxonomy_form_input">';
	echo '<label for="taxonomy_slug">' . esc_html__( 'Slug:', 'roadmapwp-pro' ) . '</label>';
	echo '<input type="text" id="taxonomy_slug" name="taxonomy_slug" required />';
	echo '</li>';
	echo '<li class="rmwp__new_taxonomy_form_input">';
	echo '<label for="taxonomy_singular">' . esc_html__( 'Singular Name:', 'roadmapwp-pro' ) . '</label>';
	echo '<input type="text" id="taxonomy_singular" name="taxonomy_singular" required />';
	echo '</li>';
	echo '<li class="rmwp__new_taxonomy_form_input">';
	echo '<label for="taxonomy_plural">' . esc_html__( 'Plural Name:', 'roadmapwp-pro' ) . '</label>';
	echo '<input type="text" id="taxonomy_plural" name="taxonomy_plural" required />';
	echo '</li>';
	echo '<li class="rmwp__new_taxonomy_form_input">';
	echo '<input type="submit" value="' . esc_attr__( 'Add Taxonomy', 'roadmapwp-pro' ) . '" />';
	echo '</li>';
	echo '</ul>';
	echo '</form>';
	echo '<hr style="margin:20px; border:2px solid #8080802e;" />';

	echo '<h2>' . esc_html__( 'Manage Taxonomies', 'roadmapwp-pro' ) . '</h2>';
	// Display existing taxonomies and their terms, including 'idea-status' and 'idea-tag'.
	$taxonomies = get_taxonomies( array( 'object_type' => array( 'idea' ) ), 'objects' );
	if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			// Exclude custom taxonomies from this loop.
			if ( ! array_key_exists( $taxonomy->name, $custom_taxonomies ) ) {
				echo '<h4>' . esc_html__( 'Taxonomy name:', 'roadmapwp-pro' ) . ' <strong>' . esc_html( $taxonomy->labels->name ) . '</strong></h4>';
				echo '<h5 style="margin-bottom: 0;">' . esc_html__( 'Terms:', 'roadmapwp-pro' ) . '</h5>';
				$terms = get_terms(
					array(
						'taxonomy'   => $taxonomy->name,
						'hide_empty' => false,
					)
				);
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					echo '<form method="post" class="delete-terms-form" data-taxonomy="' . esc_attr( $taxonomy->name ) . '">';
					echo '<ul class="terms-list">';
					foreach ( $terms as $term ) {
						echo '<li>';
						echo '<input type="checkbox" name="terms[]" value="' . esc_attr( (string) $term->term_id ) . '"> ' . esc_html( $term->name );
						echo '</li>';
					}
					echo '</ul>';
					echo '<input type="submit" value="' . esc_attr__( 'Delete Selected Terms', 'roadmapwp-pro' ) . '" class="button rmwp__delete-terms-button">';
					echo '</form>';
				} else {
					// translators: %s: taxonomy name.
					echo '<p>' . sprintf( esc_html__( 'No terms found for %s.', 'roadmapwp-pro' ), esc_html( $taxonomy->labels->name ) ) . '</p>';
				}

				echo '<form action="' . esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ) . '" method="post">';
				// translators: %s: singular taxonomy name.
				echo '<input type="text" name="new_term" placeholder="' . esc_attr( sprintf( __( 'New Term for %s', 'roadmapwp-pro' ), $taxonomy->labels->singular_name ) ) . '" />';
				echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr( $taxonomy->name ) . '" />';
				echo '<input type="submit" value="' . esc_attr__( 'Add Term', 'roadmapwp-pro' ) . '" />';
				wp_nonce_field( 'add_term_to_' . $taxonomy->name, 'wp_roadmap_add_term_nonce' );
				echo '</form>';
				echo '<hr style="margin:20px; border:2px solid #8080802e;" />';
			}
		}
	}

	// Display and provide deletion option for custom taxonomies.
	if ( ! empty( $custom_taxonomies ) && is_array( $custom_taxonomies ) ) {
		foreach ( $custom_taxonomies as $taxonomy_slug => $taxonomy_data ) {
			echo '<h3>' . esc_html( $taxonomy_data['labels']['name'] ) . '</h3>';
			echo '<a href="#" class="delete-taxonomy" data-taxonomy="' . esc_attr( $taxonomy_slug ) . '">' . esc_html__( 'Delete this taxonomy', 'roadmapwp-pro' ) . '</a>';

						  // Display and delete terms for custom taxonomies.
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy_slug,
					'hide_empty' => false,
				)
			);
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				echo '<form method="post" class="delete-terms-form" data-taxonomy="' . esc_attr( $taxonomy_slug ) . '">';
				echo '<ul class="terms-list">';
							foreach ( $terms as $term ) {
								echo '<li>';
								echo '<input type="checkbox" name="terms[]" value="' . esc_attr( (string) $term->term_id ) . '"> ' . esc_html( $term->name );
								echo '</li>';
							}
				echo '</ul>';
				echo '<input type="submit" value="' . esc_attr__( 'Delete Selected Terms', 'roadmapwp-pro' ) . '" class="button rmwp__delete-terms-button">';
				echo '</form>';
			} else {
				// translators: %s: taxonomy name.
				echo '<p>' . sprintf( esc_html__( 'No terms found for %s.', 'roadmapwp-pro' ), esc_html( $taxonomy_data['labels']['name'] ) ) . '</p>';
			}

						  // Form to add a new term to this custom taxonomy.
						  echo '<form action="' . esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ) . '" method="post">';
						  // translators: %s: singular taxonomy name.
						  echo '<input type="text" name="new_term" placeholder="' . esc_attr( sprintf( __( 'New Term for %s', 'roadmapwp-pro' ), $taxonomy_data['labels']['singular_name'] ) ) . '" />';
			echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr( $taxonomy_slug ) . '" />';
			echo '<input type="submit" value="' . esc_attr__( 'Add Term', 'roadmapwp-pro' ) . '" />';
			wp_nonce_field( 'add_term_to_' . $taxonomy_slug, 'wp_roadmap_add_term_nonce' );
			echo '</form>';

			echo '<hr style="margin:20px; border:2px solid #8080802e;" />';
		}
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
