<?php
/**
 * This file contains functions related to the registration and rendering of the 'Single Idea' block in the RoadMapWP Pro plugin.
 *
 * @package RoadMapWP\Pro\Blocks\SingleIdea
 */

namespace RoadMapWP\Pro\Blocks\SingleIdea;

/**
 * Registers custom blocks for the RoadMapWP Pro plugin.
 *
 * This function registers scripts used by the blocks and the blocks themselves, setting up render callbacks as necessary.
 */
function register_blocks() {

	$single_idea_block_path = plugin_dir_path( dirname( __DIR__ ) ) . 'build/single-idea-block';
	register_block_type_from_metadata(
		$single_idea_block_path,
		array(
			'attributes'      => array(
				'cover' => array(
					'type'    => 'string',
					'default' => '',
				),
				'selectedCourses' => array(
                    'type'    => 'array',
                    'default' => array(),
                    'items'   => array(
                        'type' => 'integer',
                    ),
                ),
			),
			'example'         => array(
				'attributes'    => array(
					'cover' => '/wp-content/plugins/roadmapwp-pro/app/assets/img/single-idea-preview.jpg',
				),
				'viewportWidth' => 800,
			),
			'render_callback' => function ( $attributes ) {

				$user_id = get_current_user_id();
				$display_block = apply_filters('roadmapwp_single_idea_block', true, $attributes, $user_id);
			
				 // Dev Note: probably a better way to do this
				 $learndash_active = function_exists('sfwd_lms_has_access');

				 // Check if any courses are selected
				 $selectedCourses = $attributes['selectedCourses'] ?? [];
				 $userHasAccess = false;

				if (!$display_block) {
					return '';
				}

				if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
					return '<p>You must be logged in to view this idea.</p>';
				}

				global $post;
				$original_post = $post;
				// Attempt to get the ideaId from the URL if available.
				$idea_id = filter_input( INPUT_GET, 'idea_id', FILTER_VALIDATE_INT );

				$post = get_post( $idea_id );
				if ( ! $post || 'idea' !== $post->post_type ) {
					return '<p>Idea not found.</p>';
				}

					// Get vote count.
					$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );

					// If LearnDash is active and courses are selected, check the user's enrollment
					if ($learndash_active && !empty($selectedCourses)) {
						foreach ($selectedCourses as $courseId) {
							if (sfwd_lms_has_access($courseId, $user_id)) {
								$userHasAccess = true;
								break; // Exit loop if user has access to at least one course
							}
						}
						
						// If the user is not enrolled in any selected courses, return without rendering the block
						if (!$userHasAccess) {
							return '';
						}
					} elseif (!empty($selectedCourses) && !$learndash_active) {
						// If LearnDash is not active but courses were selected, ignore the course selection and proceed to render
						// This ensures the block content is accessible when LearnDash is deactivated
						$userHasAccess = true; // Bypass enrollment checks
					}

					ob_start();
				?>
				<main id="primary" class="site-main">
					<div class="roadmap_wrapper container mx-auto">
						<article id="post-<?php echo esc_attr( $post->ID ); ?>" <?php post_class(); ?>>
							<header class="entry-header">
								<h1 class="entry-title"><?php echo esc_html( $post->post_title ); ?></h1>
								<p class="publish-date"><?php echo esc_html( get_the_date( '', $post ) ); ?></p>
							</header>

							<?php
							$taxonomies         = array( 'idea-tag' );
							$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', array() );
							$taxonomies         = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );
							$exclude_taxonomies = array( 'idea-status' );
							$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );
							$terms              = wp_get_post_terms( $post->ID, $taxonomies, array( 'exclude' => $exclude_taxonomies ) );

							if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								echo '<div class="idea-tags flex space-x-2 idea-tags">';
								foreach ( $terms as $term ) {
									$term_link = get_term_link( $term );
									if ( ! is_wp_error( $term_link ) ) {
										?>
									<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 text-white px-3 py-1 rounded-full text-sm !no-underline"><?php echo esc_html( $term->name ); ?></a>
										<?php
									}
								}
								echo '</div>';
							}
							?>

							<div class="entry-content">
								<?php echo wp_kses_post( apply_filters( 'the_content', $post->post_content ) ); ?>
							</div>

							<?php
								\RoadMapWP\Pro\ClassVoting\VotingHandler::render_vote_button($idea_id, $vote_count);
							?>

							<footer class="entry-footer">
								<?php edit_post_link( __( 'Edit', 'roadmapwp-pro' ), '<span class="edit-link">', '</span>', $post->ID ); ?>
							</footer>
						</article>
					</div>
				</main>
				<?php
				if ( 'idea' === get_post_type() ) {
					comments_template();
				}
					$post = $original_post;
					return ob_get_clean();
			},
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\\register_blocks' );






