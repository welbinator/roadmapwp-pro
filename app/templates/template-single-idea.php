<?php
/**
 * The Template for displaying all single posts of the 'idea' CPT.
 */

  // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

$options                = get_option( 'wp_roadmap_settings' );
$allow_comments         = isset( $options['allow_comments'] ) ? $options['allow_comments'] : false;


get_header(); ?>

<main id="primary" class="flex-grow px-4 py-8 site-main">
	<div class="roadmap_wrapper container mx-auto">
		<article class="flex flex-col space-y-8">
			<?php
			while ( have_posts() ) :
				the_post();
				$idea_id    = get_the_ID();
				$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
				?>
				<div id="post-<?php the_ID(); ?>" <?php post_class( 'p-6 flex flex-col space-y-2' ); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title text-4xl font-bold">', '</h1>' ); ?>
						<p class="publish-date text-gray-600"><?php echo esc_html( get_the_date() ); ?></p> <!-- Published date -->
					</header><!-- .entry-header -->

					<?php
					// Always include 'idea-tag' taxonomy
					$taxonomies = array( 'idea-tag' );

					// Include custom taxonomies
					$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
					$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

					// Exclude 'idea-status' taxonomy
					$exclude_taxonomies = array( 'idea-status' );
					$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );

					$terms = wp_get_post_terms( get_the_ID(), $taxonomies, array( 'exclude' => $exclude_taxonomies ) );
					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						echo '<div class="idea-tags flex space-x-2">';
						foreach ( $terms as $term ) {
							$term_link = get_term_link( $term );
							if ( ! is_wp_error( $term_link ) ) {
								?>
								
								<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 px-3 py-1 rounded-full text-sm !no-underline"><?php echo esc_html( $term->name ); ?></a>
								
								<?php
							}
						}
						echo '</div>';
					}
					?>
					
					<div class="p-6 prose prose-lg mt-4">
						<?php
							the_content();
							wp_link_pages(
								array(
									'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'roadmapwp-pro' ),
									'after'  => '</div>',
								)
							);
						?>
						</div>
						
					<div class="flex items-center gap-4 mt-4 idea-vote-box" data-idea-id="<?php echo get_the_ID(); ?>">
						<button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 text-white px-4 py-2 rounded-lg  idea-vote-button">
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
							<div class="text-white ml-2 idea-vote-count"><?php echo $vote_count; ?></div>
						</button>
					</div>
					</div>
					

					<footer class="entry-footer">
						<?php
						edit_post_link(
							sprintf(
								wp_kses(
									/* translators: %s: Name of current post. Only visible to screen readers */
									__( 'Edit <span class="screen-reader-text">%s</span>', 'roadmapwp-pro' ),
									array(
										'span' => array(
											'class' => array(),
										),
									)
								),
								wp_kses_post( get_the_title() )
							),
							'<span class="edit-link">',
							'</span>'
						);
						?>
					</footer><!-- .entry-footer -->
				

					<?php
					if ( ( $allow_comments ) && ( comments_open() || get_comments_number() ) ) :
						comments_template();
						endif;
						endwhile;
			?>
		</article>
	</div>
</main><!-- #main -->

<?php

get_footer();
?>