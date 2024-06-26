<?php
/**
 * Shortcode to display the roadmap.
 *
 * This file contains a shortcode function that renders a roadmap layout
 * with different statuses. It fetches ideas from a custom post type and
 * displays them according to their assigned status.
 */

namespace RoadMapWP\Shortcodes\Roadmap;
use RoadMapWP\Pro\Admin\Functions;
/**
 * Renders the roadmap layout with ideas sorted by status.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output for displaying the roadmap.
 */
function roadmap_shortcode( $atts ) {

	$user_id = get_current_user_id();
	$display_shortcode = true;
    $display_shortcode = apply_filters('roadmapwp_roadmap_shortcode', $display_shortcode, $user_id);

    if (!$display_shortcode) {
        return '';
    }

	// Flag to indicate the roadmap shortcode is loaded
	update_option( 'wp_roadmap_roadmap_shortcode_loaded', true );

	// Retrieve dynamic status terms
	$dynamic_status_terms = get_terms(
		array(
			'taxonomy'   => 'idea-status',
			'hide_empty' => false,
		)
	);
	$dynamic_statuses     = array_map(
		function ( $term ) {
			return $term->name;
		},
		$dynamic_status_terms
	);

	// Parse the shortcode attributes
	$atts = shortcode_atts(
		array(
			'status' => implode( ',', $dynamic_statuses ),
		),
		$atts,
		'roadmap'
	);

	$statuses = ! empty( $atts['status'] ) ? array_map( 'trim', explode( ',', $atts['status'] ) ) : $dynamic_statuses;

	// Retrieve color settings
	$options                = get_option( 'wp_roadmap_settings' );
	 

	$num_statuses  = count( $statuses );
	$md_cols_class = 'md:grid-cols-' . ( $num_statuses > 3 ? 3 : $num_statuses ); // Set to number of statuses, but max out at 4
	$lg_cols_class = 'lg:grid-cols-' . ( $num_statuses > 4 ? 4 : $num_statuses );
	$xl_cols_class = 'xl:grid-cols-' . $num_statuses;
	ob_start(); // Start output buffering

	// Always include 'idea-tag' taxonomy
	$taxonomies = array( 'idea-tag' );

	// Include custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

	// Exclude 'idea-status' taxonomy
	$exclude_taxonomies = array( 'idea-status' );
	$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );
	?>
	<div class="roadmap_wrapper container mx-auto">
	<div class="roadmap-columns grid gap-4 <?php echo esc_attr( $md_cols_class ); ?> <?php echo esc_attr( $lg_cols_class ); ?> <?php echo esc_attr( $xl_cols_class ); ?>">
			<?php
			foreach ( $statuses as $status ) {
				$args  = array(
					'post_type'      => 'idea',
					'posts_per_page' => -1,
					'tax_query'      => array(
						array(
							'taxonomy' => 'idea-status',
							'field'    => 'name',
							'terms'    => $status,
						),
					),
				);
				$query = new \WP_Query( $args );
				?>
				<div class="roadmap-column">
					<h3 style="text-align:center;"><?php echo esc_html__( $status, 'roadmapwp-pro' ); ?></h3>
					<?php
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) :
							$query->the_post();
							$idea_id    = get_the_ID();
							$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
							$idea_class = Functions\get_idea_class_with_votes($idea_id);
							?>
							<div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden m-2 <?php echo esc_attr($idea_class); ?>">
								<div class="p-6">
									<h4 class="rmwp__idea-title"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a></h4>
									<p class="text-gray-500 mt-2 mb-0 text-sm"><?php echo esc_html( get_the_date() ); ?></p>
									<div class="flex flex-wrap space-x-2 mt-2 idea-tags">
									<?php
									$terms = wp_get_post_terms( $idea_id, $taxonomies );
									foreach ( $terms as $term ) :
										$term_link = get_term_link( $term );
										if ( ! is_wp_error( $term_link ) ) :
											?>
											<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 text-white px-3 py-1 rounded-full text-sm !no-underline"><?php echo esc_html( $term->name ); ?></a>
											<?php
										endif;
									endforeach;
									?>
									</div>
									<div class="idea-excerpt mt-4"><?php echo wp_kses_post( get_the_excerpt() ); ?> <a class="text-blue-500 hover:underline" href="<?php the_permalink(); ?>" rel="ugc">read more...</a></div>
									<div class="flex items-center justify-start mt-6 gap-6">
										
									<?php
										\RoadMapWP\Pro\ClassVoting\VotingHandler::render_vote_button($idea_id, $vote_count);
									?>
									</div>
								</div>
								<?php if ( current_user_can( 'administrator' ) ) : ?>
									<div class="p-6 bg-gray-200">
										<h6 class="text-center">Admin only</h6>
										<form class="rmwp__idea-status-update-form" data-idea-id="<?php echo intval( $idea_id ); ?>">
											<select multiple class="rmwp__status-select" name="idea_status[]">
												<?php
												$statuses         = get_terms( 'idea-status', array( 'hide_empty' => false ) );
												$current_statuses = wp_get_post_terms( $idea_id, 'idea-status', array( 'fields' => 'slugs' ) );

												foreach ( $statuses as $status ) {
													$selected = in_array( $status->slug, $current_statuses ) ? 'selected' : '';
													echo '<option value="' . esc_attr( $status->slug ) . '" ' . $selected . '>' . esc_html( $status->name ) . '</option>';
												}
												?>
											</select>
											<button type="submit" class="block text-sm font-medium h-10 bg-gray-500 text-white px-4 py-2 rounded-lg update-status-button">Update</button>
										</form>
									</div>
								<?php endif; ?>
							</div>
							<?php
						endwhile;
					} else {
						echo '<p>No ideas found for ' . esc_html( $status ) . '.</p>';
					}
					wp_reset_postdata();
					?>
				</div> <!-- Close column -->
				<?php
			}
			?>
		</div> <!-- Close grid -->
	</div>
	<?php
	return ob_get_clean(); // Return the buffered output
}
add_shortcode( 'roadmap', __NAMESPACE__ . '\\roadmap_shortcode' );
