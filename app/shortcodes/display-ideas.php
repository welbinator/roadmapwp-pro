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

	$user_id = get_current_user_id();
	$display_shortcode = true;
    $display_shortcode = apply_filters('roadmapwp_display_ideas_shortcode', $display_shortcode, $user_id);

    if (!$display_shortcode) {
        return '';
    }

	// Flag to indicate the display ideas shortcode is loaded
	update_option( 'wp_roadmap_ideas_shortcode_loaded', true );

	ob_start(); // Start output buffering

	// Always include 'idea-tag' taxonomy
	$taxonomies = array( 'idea-tag' );

	// Include custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

	// Exclude 'idea-status' taxonomy
	$exclude_taxonomies = array( 'idea-status' );
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
		// Flag to check if there are any terms in the taxonomies
			$show_filters = false;

			foreach ( $taxonomies as $taxonomy_slug ) :
				$taxonomy = get_taxonomy( $taxonomy_slug );
				if ( $taxonomy && $taxonomy_slug != 'idea-status' ) :
					$terms = get_terms(
						array(
							'taxonomy'   => $taxonomy->name,
							'hide_empty' => false,
						)
					);
					if ( !empty($terms) ) {
						// Set flag to true if there are terms
						$show_filters = true;
					}
				endif;
			endforeach;

			// Conditionally render the filters-wrapper div
			if ( $show_filters ) :
				?>
				<div class="rmwp__filters-wrapper">
					<h4>Filters:</h4>
					<div class="rmwp__filters-inner">
						<?php
						// Reiterate through taxonomies to build the filters UI
						foreach ( $taxonomies as $taxonomy_slug ) :
							$taxonomy = get_taxonomy( $taxonomy_slug );
							if ( $taxonomy && $taxonomy_slug != 'idea-status' ) :
								$terms = get_terms(
									array(
										'taxonomy'   => $taxonomy->name,
										'hide_empty' => false,
									)
								);
								
								?>
							<div class="rmwp__ideas-filter-taxonomy" data-taxonomy="<?php echo esc_attr( $taxonomy_slug ); ?>">
									<label><?php echo esc_html( $taxonomy->labels->singular_name ); ?>:</label>
									<div class="rmwp__taxonomy-term-labels">
										<?php
										foreach ( $terms as $term ) {
											echo '<label class="rmwp__taxonomy-term-label">';
											echo '<input type="checkbox" name="idea_taxonomies[' . esc_attr( $taxonomy->name ) . '][]" value="' . esc_attr( $term->slug ) . '"> ';
											echo esc_html( $term->name );
											echo '</label>';
										}
										?>
									</div>
									<div class="rmwp__filter-match-type">
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
				<?php
			endif;
			?>
		</div>

		<div class="rmwp__ideas-list">

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