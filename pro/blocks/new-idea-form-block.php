<?php
/**
 * This file contains functions for registering and rendering the 'New Idea Form' block in the RoadMapWP Pro plugin.
 * It includes functions to initialize the block, handle its rendering, and process form submissions.
 */

namespace RoadMapWP\Pro\Blocks;

/**
 * Initializes the 'New Idea Form' block by registering its script and block type.
 */
function new_idea_form_block_init() {
	// Register the block script
	wp_register_script(
		'roadmapwp-pro-new-idea-form-block',
		plugin_dir_url( __FILE__ ) . '../../build/new-idea-form-block.js',
		array( 'wp-blocks', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-element', 'wp-api-fetch' )
	);

	// Register the block
	register_block_type(
		'roadmapwp-pro/new-idea-form',
		array(
			'editor_script'   => 'roadmapwp-pro-new-idea-form-block',
			'render_callback' => 'RoadMapWP\Pro\Blocks\new_idea_form_render',
		)
	);
}

/**
 * Renders the 'New Idea Form' block.
 *
 * @param array $attributes The block attributes.
 * @return string The HTML output for the new idea form.
 */
function new_idea_form_render( $attributes ) {
	update_option( 'wp_roadmap_new_idea_shortcode_loaded', true );

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

	$output = '';

	if ( isset( $_GET['new_idea_submitted'] ) && $_GET['new_idea_submitted'] == '1' ) {
		$output .= '<p>Thank you for your submission!</p>';
	}

	$hide_submit_idea_heading = apply_filters( 'wp_roadmap_hide_custom_idea_heading', false );
	$new_submit_idea_heading  = apply_filters( 'wp_roadmap_custom_idea_heading_text', 'Submit new Idea' );

	$output .= '<div class="roadmap_wrapper container mx-auto">';
	$output .= '<div class="new_idea_form__frontend" data-selected-statuses="' . esc_attr( $selected_statuses_str ) . '">';
	if ( ! $hide_submit_idea_heading ) {
		$output .= '<h2>' . esc_html( $new_submit_idea_heading ) . '</h2>';
	}

	$output .= '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	$output .= '<ul class="flex-outer">';

	$output .= '<li class="new_idea_form_input"><label for="idea_title">Title:</label>';
	$output .= '<input type="text" name="idea_title" id="idea_title" required></li>';

	$output .= '<li class="new_idea_form_input"><label for="idea_description">Description:</label>';
	$output .= '<textarea name="idea_description" id="idea_description" required></textarea></li>';

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
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$output .= '<li class="new_idea_form_input">';
					$output .= '<label>' . esc_html( $taxonomy->labels->singular_name ) . ':</label>';
					$output .= '<div class="taxonomy-term-labels">';
					foreach ( $terms as $term ) {
						$output .= '<label class="taxonomy-term-label">';
						$output .= '<input type="checkbox" name="idea_taxonomies[' . esc_attr( $taxonomy->name ) . '][]" value="' . esc_attr( $term->term_id ) . '"> ';
						$output .= esc_html( $term->name );
						$output .= '</label>';
					}
					$output .= '</div>';
					$output .= '</li>';
				}
			}
		}
	}
					$nonce   = wp_create_nonce( 'wp_roadmap_new_idea' );
					$output .= '<input type="hidden" name="wp_roadmap_new_idea_nonce" value="' . esc_attr( $nonce ) . '">';

					$output .= '<li class="new_idea_form_input"><input type="submit" value="Submit Idea"></li>';
					$output .= '</ul>';
					$output .= '</form>';
					$output .= '</div>';
					$output .= '</div>';

					return $output;
}


add_action( 'init', 'RoadMapWP\Pro\Blocks\new_idea_form_block_init' );

/**
 * Handles the submission of the new idea form block.
 */
function handle_new_idea_block_submission() {
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['idea_title'], $_POST['wp_roadmap_new_idea_nonce'] ) && wp_verify_nonce( $_POST['wp_roadmap_new_idea_nonce'], 'wp_roadmap_new_idea' ) ) {
		$title       = sanitize_text_field( $_POST['idea_title'] );
		$description = sanitize_textarea_field( $_POST['idea_description'] );
		$options = get_option( 'wp_roadmap_settings', array() );

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
add_action( 'template_redirect', 'RoadMapWP\Pro\Blocks\handle_new_idea_block_submission' );
