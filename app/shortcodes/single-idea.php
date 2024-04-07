<?php
/**
 * Shortcode to display a single idea.
 *
 * This file contains the shortcode for displaying a detailed view of a single idea,
 * including its title, content, and metadata such as vote count and taxonomy terms.
 */

namespace RoadMapWP\Pro\Shortcodes\SingleIdea;

/**
 * Renders a single idea using a shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @param bool  $is_block Flag to indicate if called from a block.
 * @return string HTML content of the single idea.
 */
	function single_idea_shortcode( $atts, $is_block = false ) {
	global $post;
	// Flag to indicate the roadmap shortcode is loaded
	update_option( 'wp_roadmap_single_idea_shortcode_loaded', true );

	$idea_id = isset( $_GET['idea_id'] ) ? intval( $_GET['idea_id'] ) : 0;
	$post    = get_post( $idea_id );

	if ( ! $post || $post->post_type !== 'idea' ) {
		return '<p>' . esc_html__( 'Idea not found.', 'roadmapwp-pro' ) . '</p>';
	}

	// Fetch options for styling (assumed to be saved in your options table)
	$options                = get_option( 'wp_roadmap_settings', array() );

	// Get vote count
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
			// Taxonomy logic
			$taxonomies         = array( 'idea-tag' );
			$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', array() );
			$taxonomies         = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );
			$exclude_taxonomies = array( 'idea-status' );
			$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );
			$terms              = wp_get_post_terms( $post->ID, $taxonomies, array( 'exclude' => $exclude_taxonomies ) );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				echo '<div class="idea-tags flex space-x-2">';
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
				<?php echo apply_filters( 'the_content', $post->post_content ); ?>
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

	if ( $is_block && 'idea' === get_post_type() ) {
		comments_template();
	}

	return ob_get_clean();
}
add_shortcode( 'single_idea', __NAMESPACE__ . '\single_idea_shortcode' );