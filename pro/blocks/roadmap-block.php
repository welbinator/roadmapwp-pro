<?php
/**
 * This file handles the registration and rendering of the 'Roadmap Block' for the RoadMapWP Pro plugin.
 * It includes functions to register the block and its script, as well as to render the block in the editor.
 *
 * @package RoadMapWP\Pro\Blocks\Roadmap
 */

namespace RoadMapWP\Pro\Blocks\Roadmap;

use RoadMapWP\Pro\Admin\Functions;
/**
 * Registers the 'Roadmap Block' and its associated script.
 */
function register_block() {

	// Register the block.
	$roadmap_block_path = plugin_dir_path( dirname( __DIR__ ) ) . 'build/roadmap-block';
	register_block_type_from_metadata(
		$roadmap_block_path,
		array(
			'render_callback' => __NAMESPACE__ . '\block_render',
			'attributes'      => array(
				'onlyLoggedInUsers' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'selectedStatuses'  => array(
					'type'    => 'object',
					'default' => array(),
				),
				'statusFilter'      => array(
					'type'    => 'string',
					'default' => 'published',
				),
			),
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\register_block' );

/**
 * Renders the 'Roadmap Block' in the block editor.
 *
 * @param array $attributes The attributes of the block.
 * @return string The rendered HTML of the block.
 */
function block_render( $attributes ) {

	if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
		return '';
	}

	// Check if selectedStatuses attribute is set and is an array.
	if ( isset( $attributes['selectedStatuses'] ) && is_array( $attributes['selectedStatuses'] ) ) {
		$selected_statuses = array_keys( array_filter( $attributes['selectedStatuses'] ) );
	} else {
		return '<p>No statuses selected.</p>';
	}

	// Check the status filter attribute.
	$include_pending = isset( $attributes['statusFilter'] ) && 'include_pending' === $attributes['statusFilter'];

	// Retrieve color settings.
	$options                = get_option( 'wp_roadmap_settings' );
	 

	$num_statuses  = count( $selected_statuses );
	$md_cols_class = 'md:grid-cols-' . ( $num_statuses > 3 ? 3 : $num_statuses );
	$lg_cols_class = 'lg:grid-cols-' . ( $num_statuses > 4 ? 4 : $num_statuses );
	$xl_cols_class = 'xl:grid-cols-' . $num_statuses;
	ob_start();

	// Always include 'idea-tag' taxonomy.
	$taxonomies = array( 'idea-tag' );

	// Include custom taxonomies.
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

	// Exclude 'status' taxonomy.
	$exclude_taxonomies = array( 'status' );
	$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );
	?>

	<div class="roadmap_wrapper container mx-auto">
	<div class="roadmap-columns grid gap-4 <?php echo esc_attr( $md_cols_class ); ?> <?php echo esc_attr( $lg_cols_class ); ?> <?php echo esc_attr( $xl_cols_class ); ?>">
			<?php
			foreach ( $selected_statuses as $status_slug ) :
				$term = get_term_by( 'slug', $status_slug, 'status' );
				if ( ! $term ) {
					continue;
				}

				$args = array(
					'post_type'      => 'idea',
					'posts_per_page' => -1,
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'tax_query'      => array(
						array(
							'taxonomy' => 'status',
							'field'    => 'slug',
							'terms'    => $status_slug,
						),
					),
				);

				// Include pending review ideas if selected.
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
							$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
							$idea_class = Functions\get_idea_class_with_votes( $idea_id );
							// Check post status if including pending reviews.
							if ( ! $include_pending && get_post_status() !== 'publish' ) {
								continue;
							}
							?>
							<div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden m-2 <?php echo esc_attr( $idea_class ); ?>">
								<div class="p-6">
								<h4 class="idea-title"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h4>
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
									
									<div class="idea-excerpt mt-4"><?php echo esc_html( get_the_excerpt() ); ?> <a class="text-blue-500 hover:underline" href="<?php echo esc_url( get_permalink() ); ?>" rel="ugc">read more...</a></div>
									<div class="flex items-center justify-start mt-6 gap-6">
									
									<div class="flex items-center idea-vote-box" data-idea-id="<?php echo intval( $idea_id ); ?>">
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
											<div class="text-white ml-2 idea-vote-count"><?php echo esc_html( $vote_count ); ?></div>
										</button>
										
									</div>
								</div>
								</div>
								<?php if ( current_user_can( 'manage_options' ) ) : ?>
									<div class="p-6 bg-gray-200">
										<h6 class="text-center">Admin only</h6>
										<form class="idea-status-update-form" data-idea-id="<?php echo intval( $idea_id ); ?>">
											<select multiple class="status-select" name="idea_status[]">
												<?php
												$statuses         = get_terms(
													array(
														'taxonomy' => 'status',
														'hide_empty' => false,
													)
												);
												$current_statuses = wp_get_post_terms( $idea_id, 'status', array( 'fields' => 'slugs' ) );

												foreach ( $statuses as $status ) {
													$selected = in_array( $status->slug, $current_statuses, true ) ? 'selected' : '';
													echo '<option value="' . esc_attr( $status->slug ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $status->name ) . '</option>';
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


