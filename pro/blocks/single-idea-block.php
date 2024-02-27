<?php
/**
 * This file contains functions related to the registration and rendering of the 'Single Idea' block in the RoadMapWP Pro plugin.
 *
 * @package RoadMapWP\Pro\Blocks\NewIdeaForm
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
			),
			'example'         => array(
				'attributes'    => array(
					'cover' => 'http://wproadmap.lndo.site/wp-content/plugins/roadmapwp-pro/app/assets/img/single-idea-preview.jpg',
				),
				'viewportWidth' => 800,
			),
			'render_callback' => function ( $attributes ) {
				if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
					return '<p>You must be logged in to view this idea.</p>';
				}

				global $post;
				$original_post = $post;
				// Attempt to get the ideaId from the URL if available.
				$idea_id = filter_input( INPUT_GET, 'idea_id', FILTER_VALIDATE_INT );

				$post = get_post( $idea_id );
				$post_data = print_r($idea_post, true);
				error_log($post_data);
				if ( ! $post || 'idea' !== $post->post_type ) {
					return '<p>Idea not found.</p>';
				}

					// Fetch options for styling.
					// $options = get_option( 'wp_roadmap_settings', array() );
					

					// Get vote count.
					$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );

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
							$exclude_taxonomies = array( 'status' );
							$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );
							$terms              = wp_get_post_terms( $post->ID, $taxonomies, array( 'exclude' => $exclude_taxonomies ) );

							if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								echo '<div class="idea-terms flex space-x-2 idea-tags">';
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

							<div class="flex items-center gap-4 mt-4 idea-vote-box" data-idea-id="<?php echo esc_attr( get_the_ID() ); ?>">
								<button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 text-white px-4 py-2 rounded-lg idea-vote-button">
									<svg
									xmlns="http://www.w3.org/2000/svg"
									width="24"
									height="24"
									viewBox="0 0 24 24"
									fill="none"
									stroke="currentColor"
									stroke-width="2"
									stroke-linecap="round"
									stroke-linejoin="round"
									class="w-5 h-5 mr-1"
									>
										<path d="M7 10v12"></path>
										<path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"></path>
									</svg>
									<div class="text-gray-600 ml-2 idea-vote-count"><?php echo esc_html( $vote_count ); ?></div>
								</button>
							</div>

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






