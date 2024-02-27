<?php
/**
 * Shortcodes for RoadMapWP Pro Plugin
 *
 * This file contains shortcodes used in the RoadMapWP Pro plugin.
 * It includes shortcodes for displaying a new idea submission form
 * and handling the submission of these ideas. The shortcodes enable
 * users to interact with the 'idea' custom post type within the plugin.
 */

namespace RoadMapWP\Pro\Shortcodes\NewIdeaForm;

/**
 * Shortcode to display the new idea submission form.
 *
 * @return string The HTML output for the new idea form.
 */
function new_idea_form_shortcode() {
	update_option( 'wp_roadmap_new_idea_form_shortcode_loaded', true );

	ob_start(); // Start output buffering

    if ( isset( $_GET['new_idea_submitted'] ) && $_GET['new_idea_submitted'] == '1' ) {
        echo '<p>Thank you for your submission!</p>';
    }

	$hide_submit_idea_heading = apply_filters( 'wp_roadmap_hide_custom_idea_heading', false );
	$new_submit_idea_heading  = apply_filters( 'wp_roadmap_custom_idea_heading_text', 'Submit new Idea' );

	$options                = get_option( 'wp_roadmap_settings' );
	$default_status_term = isset( $options['default_status_term'] ) ? $options['default_status_term'] : 'new-idea';

	?>

	<!-- Regular HTML Output -->
	<div class="roadmap_wrapper container mx-auto">
		<div class="new_idea_form__frontend">
			<?php if ( ! $hide_submit_idea_heading ) : ?>
				<h2><?php echo esc_html( $new_submit_idea_heading ); ?></h2>
			<?php endif; 
			$form_action = esc_url_raw($_SERVER['REQUEST_URI']);
			?>
			 <form action="<?php echo esc_url($form_action); ?>" method="post">
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
					$taxonomies = get_object_taxonomies( 'idea', 'objects' );
					foreach ( $taxonomies as $taxonomy ) {
						if ( $taxonomy->name !== 'status' ) {
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
					?>

					<input type="hidden" name="wp_roadmap_new_idea_shortcode_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wp_roadmap_new_idea' ) ); ?>">
					<li class="new_idea_form_input">
						<input type="submit" value="Submit Idea">
					</li>
				</ul>
			</form>
		</div>
	</div>

	<?php

	$output = ob_get_clean(); // End output buffering and capture the HTML
	return $output;
}

add_shortcode( 'new_idea_form', __NAMESPACE__ . '\\new_idea_form_shortcode' );

/**
 * Function to handle the submission of the new idea form.
 */
function handle_new_idea_submission() {
	$submission_nonce = ''; // Initialize outside to ensure scope availability
    $idea_id = 0; // Initialize to ensure scope availability

    if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['idea_title']) && isset($_POST['wp_roadmap_new_idea_shortcode_nonce'])) {
        $submission_nonce = sanitize_text_field(wp_unslash($_POST['wp_roadmap_new_idea_shortcode_nonce']));
        if (wp_verify_nonce($submission_nonce, 'wp_roadmap_new_idea')) {
            $title = sanitize_text_field($_POST['idea_title']);
            $description = sanitize_textarea_field($_POST['idea_description']);

            $options = get_option('wp_roadmap_settings', array());
            $default_idea_status = isset($options['default_idea_status']) ? $options['default_idea_status'] : 'pending';

            $idea_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => $description,
                'post_status' => $default_idea_status,
                'post_type' => 'idea',
            ]);
        }
    }

    // Ensure this conditional only runs if the nonce was verified successfully
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($submission_nonce) && $idea_id) {
        if (!empty($_POST['idea_taxonomies']) && is_array($_POST['idea_taxonomies'])) {
            foreach ($_POST['idea_taxonomies'] as $tax_slug => $term_ids) {
                if (!taxonomy_exists($tax_slug)) {
                    continue; // Skip processing for non-existing taxonomies
                }
                $term_ids = array_map('intval', $term_ids); // Ensure term IDs are integers
                wp_set_object_terms($idea_id, $term_ids, $tax_slug);
            }
        }

        $redirect_nonce = wp_create_nonce('new_idea_submitted');
        $redirect_url = add_query_arg(['new_idea_submitted' => '1', 'nonce' => $redirect_nonce], esc_url_raw( $_SERVER['REQUEST_URI'] )); // Replace with your actual URL
        wp_redirect($redirect_url);
        exit;
    }
}

add_action( 'template_redirect', __NAMESPACE__ . '\\handle_new_idea_submission' );
