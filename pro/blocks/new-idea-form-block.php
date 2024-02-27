<?php
/**
 * This file contains functions for registering and rendering the 'New Idea Form' block in the RoadMapWP Pro plugin.
 * It includes functions to initialize the block, handle its rendering, and process form submissions.
 */

namespace RoadMapWP\Pro\Blocks\NewIdeaForm;

/**
 * Initializes the 'New Idea Form' block by registering its script and block type.
 */
function block_init() {

	
	// Register the block
	$new_idea_form_block_path = plugin_dir_path(dirname(__DIR__)) . 'build/new-idea-form-block';
	register_block_type_from_metadata($new_idea_form_block_path, array(
			
			'render_callback' => __NAMESPACE__ . '\block_render',
			'attributes'      => array(
				'onlyLoggedInUsers' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'selectedStatuses' => array(
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
	error_log('New Idea Form block_render called');
	update_option( 'wp_roadmap_new_idea_form_shortcode_loaded', true );

	if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
		return; // Or simply return ''; to show nothing
	}

	$options                  = get_option( 'wp_roadmap_settings' );
	$submit_button_bg_color   = isset( $options['submit_button_bg_color'] ) ? $options['submit_button_bg_color'] : '#ff0000';
	$submit_button_text_color = isset( $options['submit_button_text_color'] ) ? $options['submit_button_text_color'] : '#ffffff';

	// Extract selected statuses from block attributes
	$selected_statuses = isset( $attributes['selectedStatuses'] ) ? $attributes['selectedStatuses'] : array();

	// Convert selected statuses to a comma-separated string
	$selected_statuses_str = implode(
		',',
		array_keys(
			array_filter(
				$selected_statuses,
				function ( $status ) {
					return $status;
				}
			)
		)
	);

	ob_start(); // Start output buffering

	if ( isset( $_GET['new_idea_submitted'] ) && $_GET['new_idea_submitted'] == '1' ) {
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

			<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
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
					// Retrieve the selected taxonomies from block attributes
					$selectedTaxonomies = isset( $attributes['selectedTaxonomies'] ) ? array_keys( array_filter( $attributes['selectedTaxonomies'] ) ) : array();
					$ideaTaxonomies     = get_object_taxonomies( 'idea', 'objects' );

					foreach ( $ideaTaxonomies as $taxonomy ) {
						if ( $taxonomy->name !== 'status' ) {
							// Display taxonomy if it's selected or if no specific taxonomies are selected
							if ( empty( $selectedTaxonomies ) || in_array( $taxonomy->name, $selectedTaxonomies, true ) ) {
								$terms = get_terms(
									array(
										'taxonomy'   => $taxonomy->name,
										'hide_empty' => false,
									)
								);
								if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
									?>
									<li class="new_idea_form_input">
										<label><?php echo esc_html( $taxonomy->labels->singular_name ); ?>:</label>
										<div class="taxonomy-term-labels">
											<?php
											foreach ( $terms as $term ) :
												?>
												<label class="taxonomy-term-label">
													<input type="checkbox" name="idea_taxonomies[<?php echo esc_attr( $taxonomy->name ); ?>][]" value="<?php echo esc_attr( $term->term_id ); ?>">
													<?php echo esc_html( $term->name ); ?>
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
						<input style="background-color: <?php echo esc_attr( $submit_button_bg_color ); ?>;color: <?php echo esc_attr( $submit_button_text_color ); ?>;" type="submit" value="Submit Idea">
					</li>
				</ul>
			</form>
		</div>
	</div>

	<?php

	$output = ob_get_clean(); // End output buffering and capture the HTML

	return $output;
}

/**
 * Handles the submission of the new idea form block.
 */
function handle_new_idea_block_submission() {
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['idea_title'], $_POST['wp_roadmap_new_idea_nonce'] ) && wp_verify_nonce( $_POST['wp_roadmap_new_idea_nonce'], 'wp_roadmap_new_idea' ) ) {
		$title       = sanitize_text_field( $_POST['idea_title'] );
		$description = sanitize_textarea_field( $_POST['idea_description'] );
		$options     = get_option( 'wp_roadmap_settings', array() );

		// default post status
		$default_wp_post_status = isset( $options['default_wp_post_status'] ) ? $options['default_wp_post_status'] : 'pending'; // Default to 'pending' if not set
		// Default status term from settings
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
				foreach ( $_POST['idea_taxonomies'] as $tax_slug => $term_ids ) {
					if ( $tax_slug !== 'status' ) {
						$term_ids = array_map( 'intval', $term_ids );
						wp_set_object_terms( $idea_id, $term_ids, $tax_slug );
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
add_action( 'template_redirect', __NAMESPACE__ . '\handle_new_idea_block_submission' );
