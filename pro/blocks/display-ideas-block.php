<?php
/**
 * This file contains functions related to the registration and rendering of the 'Display Ideas' block in the RoadMapWP Pro plugin.
 * It ensures that the block is registered correctly in WordPress and rendered using the corresponding shortcode function.
 *
 * @package RoadMapWP\Pro\Blocks\DisplayIdeas
 */

namespace RoadMapWP\Pro\Blocks\DisplayIdeas;

use RoadMapWP\Pro\Admin\Functions;

/**
 * Initializes the 'Display Ideas' block.
 *
 * Registers the block using metadata loaded from the `block.json` file.
 * Sets the render callback to the `block_render` function.
 */
function block_init() {

	$display_ideas_block_path = plugin_dir_path( dirname( __DIR__ ) ) . 'build/display-ideas-block';
	register_block_type_from_metadata(
		$display_ideas_block_path,
		array(
			'render_callback' => __NAMESPACE__ . '\block_render',
			'attributes'      => array(
				'onlyLoggedInUsers' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\\block_init' );

/**
 * Renders the 'Display Ideas' block.
 *
 * Outputs the HTML for the 'Display Ideas' block, including UI for filtering and displaying ideas.
 *
 * @param array $attributes The attributes of the block.
 * @return string The HTML content to display.
 */
function block_render( $attributes ) {

	update_option( 'wp_roadmap_display_ideas_shortcode_loaded', true );

	if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {

		return '';
	}

	ob_start();

	$taxonomies = array( 'idea-tag' );

	// Include custom taxonomies.
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

	// Exclude 'status' taxonomy.
	$exclude_taxonomies = array( 'status' );
	$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );

	// Retrieve color settings.
	$options                = get_option( 'wp_roadmap_settings' );

	// Check if the pro version is installed and settings are enabled.
	$hide_display_ideas_heading = apply_filters( 'wp_roadmap_hide_display_ideas_heading', false );
	$new_display_ideas_heading  = apply_filters( 'wp_roadmap_custom_display_ideas_heading_text', 'Browse Ideas' );

	?>
	
	<div class="roadmap_wrapper container mx-auto">
	<div class="browse_ideas_frontend">
		<?php
		if ( ! $hide_display_ideas_heading ) {
			echo '<h2>' . esc_html( $new_display_ideas_heading ) . '</h2>';
		}
		?>
		<div class="filters-wrapper">
			<h4>Filters:</h4>
			<div class="filters-inner">
				<?php
				foreach ( $taxonomies as $taxonomy_slug ) :
					$taxonomy = get_taxonomy( $taxonomy_slug );
					if ( 'status' !== $taxonomy && $taxonomy_slug ) :
						?>
						<div class="wp-roadmap-ideas-filter-taxonomy" data-taxonomy="<?php echo esc_attr( $taxonomy_slug ); ?>">
							<label><?php echo esc_html( $taxonomy->labels->singular_name ); ?>:</label>
							<div class="taxonomy-term-labels">
								<?php
								$filter_terms = get_terms(
									array(
										'taxonomy'   => $taxonomy->name,
										'hide_empty' => false,
									)
								);
								foreach ( $filter_terms as $filter_term ) {
									echo '<label class="taxonomy-term-label">';
									echo '<input type="checkbox" name="idea_taxonomies[' . esc_attr( $taxonomy->name ) . '][]" value="' . esc_attr( $filter_term->slug ) . '"> ';
									echo esc_html( $filter_term->name );
									echo '</label>';
								}
								?>
							</div>
							<div class="filter-match-type">
								<label><input type="radio" name="match_type_<?php echo esc_attr( $taxonomy->name ); ?>" value="any" checked> Any</label>
								<label><input type="radio" name="match_type_<?php echo esc_attr( $taxonomy->name ); ?>" value="all"> All</label>
							</div>
						</div>
						<?php
					endif;
				endforeach;
				?>
			</div>
		</div>
		</div>
<br />
		
		<div class="pt-2 relative mx-auto text-gray-600 flex gap-4">
			<input id="roadmap_search_input" class="grow border-2 border-gray-300 bg-white h-10 px-5 pr-16 rounded-lg text-sm focus:outline-none"
			type="search" name="search" placeholder="Search">
			<button id="roadmap_search_submit" type="submit" class="p-3">
			<svg class="text-gray-600 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg"
				xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px"
				viewBox="0 0 56.966 56.966" style="enable-background:new 0 0 56.966 56.966;" xml:space="preserve"
				width="512px" height="512px">
				<path
				d="M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23  s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92  c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17  s-17-7.626-17-17S14.61,6,23.984,6z" />
			</svg>
			</button>
		</div>


		<div class="wp-roadmap-ideas-list">

		<?php

		$args  = array(
			'post_type'      => 'idea',
			'posts_per_page' => -1,
		);
		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) :
			?>
		<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$idea_id    = get_the_ID();
				$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
				$idea_class = Functions\get_idea_class_with_votes( $idea_id );
				?>
	
				<div class="wp-roadmap-idea flex flex-col justify-between border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden <?php echo esc_attr( $idea_class ); ?>" data-v0-t="card">
					<?php include plugin_dir_path(__FILE__) . '../../app/includes/display-ideas-grid.php'; ?>
					<?php include plugin_dir_path(__FILE__) . '../../app/includes/display-ideas-admin.php'; ?>
				</div>
			<?php endwhile; ?>
		</div>
	</div>
</div>
<?php else : ?>
	<p>No ideas found.</p>
		<?php
endif;

	wp_reset_postdata();

	return ob_get_clean();
}


