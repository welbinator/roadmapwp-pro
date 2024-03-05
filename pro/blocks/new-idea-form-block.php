<?php
/**
 * This file contains functions for registering and rendering the 'New Idea Form' block in the RoadMapWP Pro plugin.
 * It includes functions to initialize the block, handle its rendering, and process form submissions.
 *
 * @package RoadMapWP\Pro\Blocks\NewIdeaForm
 */

namespace RoadMapWP\Pro\Blocks\NewIdeaForm;

/**
 * Initializes the 'New Idea Form' block by registering its script and block type.
 */
function block_init() {

	// Register the block.
	$new_idea_form_block_path = plugin_dir_path( dirname( __DIR__ ) ) . 'build/new-idea-form-block';
	register_block_type_from_metadata(
		$new_idea_form_block_path,
		array(

			'render_callback' => __NAMESPACE__ . '\block_render',
			'attributes'      => array(
				'onlyLoggedInUsers'  => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'selectedStatuses'   => array(
					'type'    => 'object',
					'default' => array(),
				),
				'selectedTaxonomies' => array(
					'type'    => 'object',
					'default' => array(),
				),

			),
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\block_init' );



/**
 * Renders the 'New Idea Form' block.
 *
 * @param array $attributes The block attributes.
 * @return string The HTML output for the new idea form.
 */
function block_render( $attributes ) {
	update_option( 'wp_roadmap_new_idea_form_shortcode_loaded', true );

	if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
		return;
	}

	$options             = get_option( 'wp_roadmap_settings' );
	$default_status_term = isset( $options['default_status_term'] ) ? $options['default_status_term'] : 'new-idea';

	// Extract selected statuses from block attributes.
	$selected_statuses = isset( $attributes['selectedStatuses'] ) ? $attributes['selectedStatuses'] : array();

	// Cleanup selected statuses - remove any entries that are empty or considered falsy.
	$selected_statuses = array_filter(
		$selected_statuses,
		function ( $value ) {
			return ! empty( $value );
		}
	);

	// If no statuses have been selected (or if all selected statuses are removed), add the default status term.
	if ( empty( $selected_statuses ) ) {
		// Ensure the default status term exists and get its term ID.
		$idea_term = term_exists( $default_status_term, 'status' );

		if ( $idea_term !== 0 && $idea_term !== null ) {

			// Use the term's ID directly to update $selected_statuses.
			$selected_statuses[ $idea_term['term_id'] ] = true; // Adjust this line

		}
	}

	// Convert selected statuses to a comma-separated string for display or further processing.
	$selected_statuses_str = implode(
		',',
		array_keys(
			$selected_statuses
		)
	);

	ob_start();

	if ( isset( $_GET['new_idea_submitted'] ) && '1' === $_GET['new_idea_submitted'] ) {
		echo '<p>Thank you for your submission!</p>';
	}

	$hide_submit_idea_heading = apply_filters( 'wp_roadmap_hide_custom_idea_heading', false );
	$new_submit_idea_heading  = apply_filters( 'wp_roadmap_custom_idea_heading_text', 'Submit new Idea' );
	?>

	<!-- Regular HTML Output -->
	<div class="roadmap_wrapper container mx-auto">
		<div class="new_idea_form__frontend" data-selected-statuses="<?php echo esc_attr( $selected_statuses_str ); ?>">
			<?php if ( ! $hide_submit_idea_heading ) : ?>
				<h2><?php echo esc_html( $new_submit_idea_heading ); ?></h2>
			<?php endif; ?>

			<?php
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$form_action_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			?>

			<form action="<?php echo esc_url( $form_action_url ); ?>" method="post">
				<ul class="flex-outer">
					<li class="new_idea_form_input">
						<label for="idea_title">Title:</label>
						<input type="text" name="idea_title" id="idea_title" required>
					</li>

					<li class="new_idea_form_input">
						<label for="idea_description">Description:</label>
						<textarea name="idea_description" id="idea_description" required></textarea>
					</li>

					<?php
					// Retrieve the selected taxonomies from block attributes.
					$selected_taxonomies = isset( $attributes['selectedTaxonomies'] ) ? array_keys( array_filter( $attributes['selectedTaxonomies'] ) ) : array();
					$idea_taxonomies     = get_object_taxonomies( 'idea', 'objects' );

					foreach ( $idea_taxonomies as $taxonomy ) {
						if ( 'status' !== $taxonomy->name ) {

							// Display taxonomy if it's selected or if no specific taxonomies are selected.
							if ( empty( $selected_taxonomies ) || in_array( $taxonomy->name, $selected_taxonomies, true ) ) {
								$idea_terms = get_terms(
									array(
										'taxonomy'   => $taxonomy->name,
										'hide_empty' => false,
									)
								);
								if ( ! empty( $idea_terms ) && ! is_wp_error( $idea_terms ) ) :
									?>
									<li class="new_idea_form_input">
										<label><?php echo esc_html( $taxonomy->labels->singular_name ); ?>:</label>
										<div class="taxonomy-term-labels">
											<?php
											foreach ( $idea_terms as $idea_term ) :
												?>
												<label class="taxonomy-term-label">
													<input type="checkbox" name="idea_taxonomies[<?php echo esc_attr( $taxonomy->name ); ?>][]" value="<?php echo esc_attr( $idea_term->term_id ); ?>">
													<?php echo esc_html( $idea_term->name ); ?>
												</label>
												<?php
											endforeach;
											?>
										</div>
									</li>
									<?php
								endif;
							}
						}
					}
					?>

					<input type="hidden" name="wp_roadmap_new_idea_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wp_roadmap_new_idea' ) ); ?>">
					<li class="new_idea_form_input">
						<input type="submit" value="Submit Idea">
					</li>
				</ul>
			</form>
		</div>
	</div>

	<?php

	$output = ob_get_clean();

	return $output;
}

/**
 * Handles the submission of the new idea form block.
 */
function handle_new_idea_block_submission() {
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['idea_title'], $_POST['wp_roadmap_new_idea_nonce'] ) ) {
		$nonce = sanitize_text_field( wp_unslash( $_POST['wp_roadmap_new_idea_nonce'] ) );
		if ( wp_verify_nonce( $nonce, 'wp_roadmap_new_idea' ) ) {

			if ( isset( $_POST['idea_title'] ) ) {
				$title = sanitize_text_field( wp_unslash( $_POST['idea_title'] ) );
			}
			if ( isset( $_POST['idea_description'] ) ) {
				$description = sanitize_textarea_field( wp_unslash( $_POST['idea_description'] ) );
			}
			$options = get_option( 'wp_roadmap_settings', array() );

			// default post status.
			$default_wp_post_status = isset( $options['default_wp_post_status'] ) ? $options['default_wp_post_status'] : 'pending'; // Default to 'pending' if not set
			// Default status term from settings.
			$default_idea_status_term = isset( $options['default_status_term'] ) ? $options['default_status_term'] : 'new-idea';

			$idea_id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => $description,
					'post_status'  => $default_wp_post_status,
					'post_type'    => 'idea',
				)
			);

			if ( $idea_id && ! is_wp_error( $idea_id ) ) {
				// Set terms for non-status taxonomies
				if ( isset( $_POST['idea_taxonomies'] ) && is_array( $_POST['idea_taxonomies'] ) ) {
					foreach ( $_POST['idea_taxonomies'] as $tax_slug => $idea_term_ids ) {
						if ( $tax_slug !== 'status' ) {
							$idea_term_ids = array_map( 'intval', $idea_term_ids );
							wp_set_object_terms( $idea_id, $idea_term_ids, $tax_slug );
						}
					}
				}
	
				// Check if selected statuses is set, not empty, and contains valid numeric values
				$valid_selected_statuses = isset( $_POST['selected_statuses'] ) && is_array( $_POST['selected_statuses'] )
											&& count( array_filter( $_POST['selected_statuses'], 'is_numeric' ) ) > 0;
	
				if ( $valid_selected_statuses ) {
					$selected_status_terms = array_map( 'intval', $_POST['selected_statuses'] );
					wp_set_object_terms( $idea_id, $selected_status_terms, 'status' );
				} else {
					// Fallback to default status term if none or invalid selected
					wp_set_object_terms( $idea_id, array( $default_idea_status_term ), 'status' );
				}
	
				// Redirect to the confirmation page
				$redirect_url = add_query_arg( 'new_idea_submitted', '1', esc_url_raw( $_SERVER['REQUEST_URI'] ) );
				wp_redirect( $redirect_url );
				exit;
			}
		}
	}
}
	add_action( 'template_redirect', __NAMESPACE__ . '\handle_new_idea_block_submission' );
