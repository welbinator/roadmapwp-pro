<?php
/**
 * Display idea grid item (admin/loop include).
 *
 * @package RoadMapWP\Pro\Includes
 */

use RoadMapWP\Pro\Admin\Functions;

?>

<div class="p-6">
	<?php
	// Ensure expected variables are defined when this template is included.
	$idea_id    = isset( $idea_id ) ? intval( $idea_id ) : 0;
	$taxonomies = isset( $taxonomies ) && is_array( $taxonomies ) ? $taxonomies : array();
	$vote_count = isset( $vote_count ) ? intval( $vote_count ) : 0;
	?>

	<h2 class="text-2xl font-bold"><a href="<?php echo esc_url( get_permalink( $idea_id ) ); ?>"><?php echo esc_html( get_the_title( $idea_id ) ); ?></a></h2>

	<p class="text-gray-500 mt-2 text-sm"><?php echo esc_html__( 'Submitted on:', 'roadmapwp-pro' ) . ' ' . esc_html( get_the_date( '', $idea_id ) ); ?></p>
	<div class="flex flex-wrap space-x-2 mt-2 idea-tags">
		<?php
	$tax_list = (array) $taxonomies;
		$terms    = wp_get_post_terms( $idea_id, $tax_list );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term_item ) :
				$term_link = get_term_link( $term_item );
				if ( ! is_wp_error( $term_link ) ) :
					?>
					<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 text-white px-3 py-1 rounded-full text-sm !no-underline"><?php echo esc_html( $term_item->name ); ?></a>
					<?php
				endif;
			endforeach;
		}
		?>
	</div>

	<p class="text-gray-700 mt-4 break-all">
		<?php
			$trimmed_excerpt = wp_trim_words( get_the_excerpt( $idea_id ), 20 );
			echo esc_html( $trimmed_excerpt );
			printf( ' <a class="text-blue-500 hover:underline" href="%s" rel="ugc">%s</a>', esc_url( get_permalink( $idea_id ) ), esc_html__( 'read more...', 'roadmapwp-pro' ) );
		?>
	</p>

	<div class="flex items-center justify-start mt-6 gap-6">
	<?php
		// If a vote count was provided, normalize it; otherwise fetch from post meta.
		if ( empty( $vote_count ) ) {
			$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
		} else {
			$vote_count = intval( $vote_count );
		}
	if ( class_exists( '\RoadMapWP\Pro\ClassVoting\VotingHandler' ) ) {
		\RoadMapWP\Pro\ClassVoting\VotingHandler::render_vote_button( $idea_id, $vote_count );
	}
	?>
	</div>
</div>