<?php
/**
 * Shortcode to display a single idea.
 *
 * This file contains the shortcode for displaying a detailed view of a single idea,
 * including its title, content, and metadata such as vote count and taxonomy terms.
 *
 * @package RoadMapWP\Pro\Shortcodes
 */

namespace RoadMapWP\Pro\Shortcodes\SingleIdea;

/**
 * Gets a term ID by its slug.
 *
 * @param string $slug The term slug.
 * @return int Term ID if found, 0 otherwise.
 */
function get_term_by_slug( $slug ) {
	$term = get_term_by( 'slug', $slug, 'idea-status' );
	return $term ? (int) $term->term_id : 0;
}

/**
 * Renders a single idea using a shortcode.
 *
 * Displays a detailed view of a single idea post type, including its title,
 * content, metadata (vote count, taxonomy terms), and optionally comments.
 *
 * @param array $atts    Shortcode attributes (currently unused).
 * @param bool  $is_block Flag to indicate if called from a block context.
 * @return string HTML content of the single idea or error message if idea not found.
 */
function single_idea_shortcode( $atts, $is_block = false ) {

	$user_id           = get_current_user_id();
	$display_shortcode = true;
	$display_shortcode = apply_filters( 'roadmapwp_single_idea_shortcode', $display_shortcode, $user_id );

	if ( ! $display_shortcode ) {
		return '';
	}

	// Flag to indicate the single idea shortcode is loaded.
	update_option( 'wp_roadmap_single_idea_shortcode_loaded', true );

	// Get and validate the idea ID.
	$idea_id = 0;
	if ( isset( $_GET['idea_id'] ) && isset( $_GET['_wpnonce'] ) ) {
		$nonce_valid = wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'view_idea_' . absint( $_GET['idea_id'] ) );
		if ( $nonce_valid ) {
			$idea_id = absint( sanitize_text_field( wp_unslash( $_GET['idea_id'] ) ) );
		}
	}
	$idea = get_post( $idea_id );

	if ( ! $idea || 'idea' !== $idea->post_type ) {
		return '<p>' . esc_html__( 'Idea not found.', 'roadmapwp-pro' ) . '</p>';
	}

	// Fetch options for styling (assumed to be saved in your options table).
	$options = get_option( 'wp_roadmap_settings', array() );

	// Get vote count.
	$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );

	ob_start();
	?>
	<main id="primary" class="site-main">
		<div class="roadmap_wrapper container mx-auto">
		<article id="post-<?php echo esc_attr( (string) $idea->ID ); ?>" <?php post_class( '', $idea ); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php echo esc_html( $idea->post_title ); ?></h1>
				<p class="publish-date"><?php echo esc_html( get_the_date( '', $idea ) ); ?></p>
			</header>

		<?php
		// Taxonomy logic.
		$taxonomies         = array( 'idea-tag' );
		$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', array() );
		$taxonomies         = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );
		$exclude_taxonomies = array( 'idea-status' );
		$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );
		// Get terms for current idea while excluding status terms.
		$term_args = array(
			'exclude' => array_map( __NAMESPACE__ . '\\get_term_by_slug', $exclude_taxonomies ),
		);
		$terms     = wp_get_post_terms( $idea->ID, $taxonomies, $term_args );

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
			<?php echo wp_kses_post( apply_filters( 'the_content', $idea->post_content ) ); ?>
			</div>

			<?php
			if ( class_exists( '\RoadMapWP\Pro\ClassVoting\VotingHandler' ) ) {
				\RoadMapWP\Pro\ClassVoting\VotingHandler::render_vote_button( $idea_id, $vote_count );
			}
			?>

			<footer class="entry-footer">
			<?php edit_post_link( __( 'Edit', 'roadmapwp-pro' ), '<span class="edit-link">', '</span>', $idea->ID ); ?>
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