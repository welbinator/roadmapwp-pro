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
				'cover'           => array(
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
					'cover' => 'http://wproadmap.lndo.site/wp-content/plugins/roadmapwp-pro/app/assets/img/single-idea-preview.jpg',
				),
				'viewportWidth' => 800,
			),
			'render_callback' => function ( $attributes ) {

				$user_id = get_current_user_id();
				$display_block = apply_filters( 'roadmapwp_single_idea_block', true, $attributes, $user_id );

				// Dev Note: probably a better way to do this
				$learndash_active = function_exists( 'sfwd_lms_has_access' );

				// Check if any courses are selected
				$selectedCourses = $attributes['selectedCourses'] ?? array();
				$userHasAccess = false;

				if ( ! $display_block ) {
					return '';
				}

				if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
					return '<p>You must be logged in to view this idea.</p>';
				}

				global $post;
				$original_post = $post;
				// Debug: log the incoming context (only when WP_DEBUG enabled)
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'RoadMapWP Debug: single-idea block render_callback entered. global_post_id=' . ( isset( $post->ID ) ? $post->ID : 'none' ) . ' REQUEST_URI=' . ( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '' ) );
				}

				// Attempt to get the ideaId from the URL if available. If not present,
				// fall back to the current global post when rendering a single 'idea'.
				$idea_id = filter_input( INPUT_GET, 'idea_id', FILTER_VALIDATE_INT );

				if ( $idea_id ) {
					$post = get_post( $idea_id );
				} else {
					// If the global post is already an 'idea' (e.g. when using the "Choose Page" template), use it.
					if ( isset( $post ) && is_object( $post ) && 'idea' === get_post_type( $post ) ) {
						$idea_id = $post->ID;
					} else {
						return '<p>Idea not found.</p>';
					}
				}

				if ( ! $post || 'idea' !== get_post_type( $post ) ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( 'RoadMapWP Debug: single-idea block: idea not found. idea_id=' . var_export( $idea_id, true ) . ' resolved_post_type=' . ( is_object( $post ) ? get_post_type( $post ) : 'none' ) );
					}
					return '<p>Idea not found.</p>';
				}

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'RoadMapWP Debug: single-idea block rendering idea_id=' . $post->ID );
				}

					// Get vote count.
					$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );

					// If LearnDash is active and courses are selected, check the user's enrollment
				if ( $learndash_active && ! empty( $selectedCourses ) ) {
					foreach ( $selectedCourses as $courseId ) {
						if ( sfwd_lms_has_access( $courseId, $user_id ) ) {
							$userHasAccess = true;
							break; // Exit loop if user has access to at least one course
						}
					}

					// If the user is not enrolled in any selected courses, return without rendering the block
					if ( ! $userHasAccess ) {
						return '';
					}
				} elseif ( ! empty( $selectedCourses ) && ! $learndash_active ) {
					// If LearnDash is not active but courses were selected, ignore the course selection and proceed to render
					// This ensures the block content is accessible when LearnDash is deactivated
					$userHasAccess = true; // Bypass enrollment checks
				}

					ob_start();
				?>
				<main id="primary" class="site-main">
					<div class="roadmap_wrapper container mx-auto">
						<article id="post-<?php echo esc_attr( (string) $post->ID ); ?>" <?php post_class(); ?>>
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
							// We already filtered $taxonomies to exclude the status taxonomy above, so fetch terms directly.
							$terms              = wp_get_post_terms( $post->ID, $taxonomies );

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
								\RoadMapWP\Pro\ClassVoting\VotingHandler::render_vote_button( $idea_id, $vote_count );
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






