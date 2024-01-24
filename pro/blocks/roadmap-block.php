<?php
/**
 * This file handles the registration and rendering of the 'Roadmap Block' for the RoadMapWP Pro plugin.
 * It includes functions to register the block and its script, as well as to render the block in the editor.
 */

namespace RoadMapWP\Pro\Blocks;

/**
 * Registers the 'Roadmap Block' and its associated script.
 */
function register_roadmap_block() {
	// Register the block script
	wp_register_script(
		'roadmapwp-pro-roadmap-block',
		plugin_dir_url( __FILE__ ) . '../../build/roadmap-block.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-api-fetch' )
	);

	// Register the block
	register_block_type(
		'roadmapwp-pro/roadmap-block',
		array(
			'editor_script'   => 'roadmapwp-pro-roadmap-block',
			'render_callback' => 'RoadMapWP\Pro\Blocks\roadmap_block_render',
		)
	);
}

add_action( 'init', 'RoadMapWP\Pro\Blocks\register_roadmap_block' );

/**
 * Renders the 'Roadmap Block' in the block editor.
 *
 * @param array $attributes The attributes of the block.
 * @return string The rendered HTML of the block.
 */
function roadmap_block_render( $attributes ) {
	// Check if selectedStatuses attribute is set and is an array
	if ( isset( $attributes['selectedStatuses'] ) && is_array( $attributes['selectedStatuses'] ) ) {
		$selected_statuses = array_keys( array_filter( $attributes['selectedStatuses'] ) );
	} else {
		// If no statuses are selected, you can choose to return nothing or handle it differently
		return '<p>No statuses selected.</p>';
	}

	// Check the status filter attribute
	$include_pending = isset( $attributes['statusFilter'] ) && $attributes['statusFilter'] === 'include_pending';

	// Retrieve color settings
	$options            = get_option( 'wp_roadmap_settings' );
	$vote_button_bg_color   = isset( $options['vote_button_bg_color'] ) ? $options['vote_button_bg_color'] : '#ff0000';
	$vote_button_text_color = isset( $options['vote_button_text_color'] ) ? $options['vote_button_text_color'] : '#ffffff';
	$filter_tags_bg_color   = isset( $options['filter_tags_bg_color'] ) ? $options['filter_tags_bg_color'] : '#ff0000';
	$filter_tags_text_color = isset( $options['filter_tags_text_color'] ) ? $options['filter_tags_text_color'] : '#ffffff';

	$num_statuses  = count( $selected_statuses );
	$md_cols_class = 'md:grid-cols-' . ( $num_statuses > 3 ? 3 : $num_statuses ); // Set to number of statuses, but max out at 3
	$lg_cols_class = 'lg:grid-cols-' . ( $num_statuses > 4 ? 4 : $num_statuses );
	$xl_cols_class = 'xl:grid-cols-' . $num_statuses;
	ob_start();

	// Always include 'idea-tag' taxonomy
	$taxonomies = array( 'idea-tag' );

	// Include custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

	// Exclude 'status' taxonomy
	$exclude_taxonomies = array( 'status' );
	$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );
	?>

	<div class="roadmap_wrapper container mx-auto">
		<div class="roadmap-columns grid gap-4 <?php echo $md_cols_class; ?> <?php echo $lg_cols_class; ?> <?php echo $xl_cols_class; ?>">
			<?php
			foreach ( $selected_statuses as $status_slug ) :
				$term = get_term_by( 'slug', $status_slug, 'status' );
				if ( ! $term ) {
					continue; // Skip if term not found
				}

				$args = array(
					'post_type'      => 'idea',
					'posts_per_page' => -1,
					'tax_query'      => array(
						array(
							'taxonomy' => 'status',
							'field'    => 'slug',
							'terms'    => $status_slug,
						),
					),
				);

				// Include pending review ideas if selected
				if ( $include_pending ) {
					$args['post_status'] = array( 'publish', 'pending' );
				}

				$query = new \WP_Query( $args );
				?>

				<div class="roadmap-column">
					<h3 style="text-align:center;"><?php echo esc_html( $term->name ); ?></h3>
					<?php
					if ( $query->have_posts() ) :
						while ( $query->have_posts() ) :
							$query->the_post();
							$idea_id    = get_the_ID();
							$vote_count = get_post_meta( $idea_id, 'idea_votes', true ) ?: '0';
							// Check post status if including pending reviews
							if ( ! $include_pending && get_post_status() !== 'publish' ) {
								continue;
							}
							?>
							<div class="border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden m-2 wp-roadmap-idea">
								<div class="p-6">
									<h4 class="idea-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h4>
									<p class="text-gray-500 mt-2 mb-0 text-sm"><?php echo get_the_date(); ?></p>
									<div class="flex flex-wrap space-x-2 mt-2 idea-tags">
									<?php
									$terms = wp_get_post_terms( $idea_id, $taxonomies );
									foreach ( $terms as $term ) :
										$term_link = get_term_link( $term );
										if ( ! is_wp_error( $term_link ) ) :
											?>
											<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 px-3 py-1 rounded-full text-sm !no-underline" style="background-color: <?php echo esc_attr( $filter_tags_bg_color ); ?>;color: <?php echo esc_attr( $filter_tags_text_color ); ?>;"><?php echo esc_html( $term->name ); ?></a>
											<?php
										endif;
									endforeach;
									?>
									</div>
									
									<div class="idea-excerpt mt-4"><?php echo get_the_excerpt(); ?> <a class="text-blue-500 hover:underline" href="<?php the_permalink(); ?>" rel="ugc">read more...</a></div>
									<div class="flex items-center justify-start mt-6 gap-6">
									
									<div class="flex items-center idea-vote-box" data-idea-id="<?php echo $idea_id; ?>">
										<button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 px-4 py-2 rounded-lg idea-vote-button" style="background-color: <?php echo esc_attr( $vote_button_bg_color ); ?>!important;background-image: none!important;color: <?php echo esc_attr( $vote_button_text_color ); ?>!important;">
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
											Vote
										</button>
										<div class="text-gray-600 ml-2 idea-vote-count"><?php echo $vote_count; ?> votes</div>
									</div>
								</div>
								</div>
								<?php if ( current_user_can( 'administrator' ) ) : ?>
									<div class="p-6 bg-gray-200">
										<h6 class="text-center">Admin only</h6>
										<form class="idea-status-update-form" data-idea-id="<?php echo $idea_id; ?>">
											<select multiple class="status-select" name="idea_status[]">
												<?php
												$statuses         = get_terms( 'status', array( 'hide_empty' => false ) );
												$current_statuses = wp_get_post_terms( $idea_id, 'status', array( 'fields' => 'slugs' ) );

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
						else :
							?>
						<p>No ideas found for <?php echo esc_html( $term->name ); ?>.</p>
							<?php
					endif;
						wp_reset_postdata();
						?>
				</div> <!-- Close column -->
			<?php endforeach; ?>
		</div> <!-- Close grid -->
	</div>

		<?php
		return ob_get_clean();
}


