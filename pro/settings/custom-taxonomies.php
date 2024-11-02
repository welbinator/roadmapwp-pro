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
	$error_message   = '';

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
		if ( term_exists( $new_term, $taxonomy_slug ) ) { ?>
			<div class="error">
				<p>Term already exists in this taxonomy.</p>
			</div>
		<?php
		} else {
			$inserted_term = wp_insert_term( $new_term, $taxonomy_slug );
			if ( is_wp_error( $inserted_term ) ) { ?>
				<div class="error">
					<p>Term could not be added: <?php echo esc_html( $inserted_term->get_error_message() ); ?></p>
				</div>
			<?php
			} else { ?>
				<div class="updated">
					<p>Term added successfully.</p>
				</div>
			<?php
			}
		}
	}

	// Check if the form has been submitted for adding a new taxonomy
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['wp_roadmap_pro_nonce'], $_POST['taxonomy_slug'] ) && empty( $_POST['new_term'] ) ) {
		if ( wp_verify_nonce( $_POST['wp_roadmap_pro_nonce'], 'wp_roadmap_pro_add_taxonomy' ) ) {
			$taxonomy_slug     = sanitize_key( $_POST['taxonomy_slug'] );
			$taxonomy_singular = sanitize_text_field( $_POST['taxonomy_singular'] );
			$taxonomy_plural   = sanitize_text_field( $_POST['taxonomy_plural'] );

			// Check if the slug is "type"
			if ( $taxonomy_slug === 'type' ) {
				$error_message = esc_html__( '\'type\' is a reserved term and cannot be used as the slug for a custom taxonomy', 'roadmapwp-pro' );
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

	if ( ! empty( $error_message ) ) { ?>
		<div class="error">
			<p><?php echo $error_message; ?></p>
		</div>
	<?php } ?>

	<div class="wrap custom">

	<br />
		<h3>Add Taxonomy (filter)</h3>
		<form method="post">
			<?php wp_nonce_field( 'wp_roadmap_pro_add_taxonomy', 'wp_roadmap_pro_nonce' ); ?>
			<ul class="rmwp__flex-outer">
				<li class="rmwp__new_taxonomy_form_input">
					<label for="taxonomy_slug">Slug:</label>
					<input type="text" id="taxonomy_slug" name="taxonomy_slug" required>
				</li>
				<li class="rmwp__new_taxonomy_form_input">
					<label for="taxonomy_singular">Singular Nameee:</label>
					<input type="text" id="taxonomy_singular" name="taxonomy_singular" required>
				</li>
				<li class="rmwp__new_taxonomy_form_input">
					<label for="taxonomy_plural">Plural Name:</label>
					<input type="text" id="taxonomy_plural" name="taxonomy_plural" required>
				</li>
				<li class="rmwp__new_taxonomy_form_input">
					<input type="submit" value="Add Taxonomy">
				</li>
			</ul>
		</form>
		<hr style="margin:20px; border:2px solid #8080802e;" />

		<h3>Manage Taxonomies</h3>

		<?php
		


		// Display and provide deletion option for custom taxonomies
		foreach ( $custom_taxonomies as $taxonomy_slug => $taxonomy_data ) { ?>
			<h3><?php echo esc_html( $taxonomy_data['labels']['name'] ); ?></h3>
			<a href="#" class="delete-taxonomy" data-taxonomy="<?php echo esc_attr( $taxonomy_slug ); ?>">Delete this taxonomyyy</a>

			<?php
			// Display and delete terms for custom taxonomies
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy_slug,
					'hide_empty' => false,
				)
			);
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) { ?>
				<form method="post" class="delete-terms-form" data-taxonomy="<?php echo esc_attr( $taxonomy_slug ); ?>">
					<ul class="terms-list">
						<?php foreach ( $terms as $term ) { ?>
							<li>
								<input type="checkbox" name="terms[]" value="<?php echo esc_attr( $term->term_id ); ?>"> <?php echo esc_html( $term->name ); ?>
							</li>
						<?php } ?>
					</ul>
					<input type="submit" value="Delete Selected Terms" class="button rmwp__delete-terms-button">
				</form>
			<?php } else { ?>
				<p>No terms found for <?php echo esc_html( $taxonomy_data['labels']['name'] ); ?>.</p>
			<?php } ?>

			<form action="<?php echo esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ); ?>" method="post">
				<input type="text" name="new_term" placeholder="New Term for <?php echo esc_attr( $taxonomy_data['labels']['singular_name'] ); ?>" />
				<input type="hidden" name="taxonomy_slug" value="<?php echo esc_attr( $taxonomy_slug ); ?>" />
				<input type="submit" value="Add Term" />
				<?php wp_nonce_field( 'add_term_to_' . $taxonomy_slug, 'wp_roadmap_add_term_nonce' ); ?>
			</form>
			<hr style="margin:20px; border:2px solid #8080802e;" />
		<?php }

		if ( $should_redirect ) { ?>
			<script type="text/javascript">
				window.location.href = "<?php echo esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ); ?>";
			</script>
		<?php } ?>
	</div>

	<?php
	$content = ob_get_clean(); // Get the buffered output

	echo $content;


	return $content; // Return the content as usual
}




