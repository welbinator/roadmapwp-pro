<?php
/**
 * Shortcode for Displaying Ideas in RoadMapWP Pro.
 *
 * This file contains functions for a shortcode used in the RoadMapWP Pro plugin.
 * This shortcodes are responsible for rendering and displaying ideas in various formats.
 *
 * @package RoadMapWP\Pro\Shortcodes\DisplayIdeas
 */

namespace RoadMapWP\Pro\Shortcodes\DisplayIdeas;
use RoadMapWP\Pro\Admin\Functions;
/**
 * Shortcode to display ideas.
 *
 * Outputs a collection of ideas in a grid or list format, depending on the implementation.
 * This shortcode allows users to view ideas posted within the RoadMapWP Pro system.
 *
 * @return string The HTML output for displaying ideas.
 */
function display_ideas_shortcode() {
	// Flag to indicate the display ideas shortcode is loaded
	update_option( 'wp_roadmap_ideas_shortcode_loaded', true );

	ob_start(); // Start output buffering

	// Always include 'idea-tag' taxonomy
	$taxonomies = array( 'idea-tag' );

	// Include custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

	// Exclude 'status' taxonomy
	$exclude_taxonomies = array( 'status' );
	$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );

	// Retrieve color settings
	$options                = get_option( 'wp_roadmap_settings' );

	// Check if the pro version is installed and settings are enabled
	$hide_display_ideas_heading = apply_filters( 'wp_roadmap_hide_display_ideas_heading', false );
	$new_display_ideas_heading  = apply_filters( 'wp_roadmap_custom_display_ideas_heading_text', 'Browse Ideas' );
	?>
	
	<div class="roadmap_wrapper container mx-auto">
	<div class="browse_ideas_frontend">
		<?php
		$output = '<h2>' . esc_html( $new_display_ideas_heading ) . '</h2>';
		if ( ! $hide_display_ideas_heading ) {
			echo wp_kses_post( $output );
		}
		?>
		<div class="filters-wrapper">
			<h4>Filters:</h4>
			<div class="filters-inner">
				<?php
				foreach ( $taxonomies as $taxonomy_slug ) :
					$taxonomy = get_taxonomy( $taxonomy_slug );
					if ( $taxonomy && $taxonomy_slug != 'status' ) :
						?>
						<div class="wp-roadmap-ideas-filter-taxonomy" data-taxonomy="<?php echo esc_attr( $taxonomy_slug ); ?>">
							<label><?php echo esc_html( $taxonomy->labels->singular_name ); ?>:</label>
							<div class="taxonomy-term-labels">
								<?php
								$terms = get_terms(
									array(
										'taxonomy'   => $taxonomy->name,
										'hide_empty' => false,
									)
								);
								foreach ( $terms as $term ) {
									echo '<label class="taxonomy-term-label">';
									echo '<input type="checkbox" name="idea_taxonomies[' . esc_attr( $taxonomy->name ) . '][]" value="' . esc_attr( $term->slug ) . '"> ';
									echo esc_html( $term->name );
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

		<div class="wp-roadmap-ideas-list">

		<?php
		$args  = array(
			'post_type'      => 'idea',
			'posts_per_page' => -1, // Adjust as needed
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
					$idea_class = Functions\get_idea_class_with_votes($idea_id);
					?>
		
					<div class="wp-roadmap-idea flex flex-col justify-between border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden <?php echo esc_attr($idea_class); ?>" data-v0-t="card">	
						<?php include plugin_dir_path(__FILE__) . '../includes/display-ideas-grid.php'; ?>
						<?php include plugin_dir_path(__FILE__) . '../includes/display-ideas-admin.php'; ?>
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

	return ob_get_clean(); // Return the buffered output
}

add_shortcode( 'display_ideas', __NAMESPACE__ . '\\display_ideas_shortcode' );