<?php use RoadMapWP\Pro\Admin\Functions; ?>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$idea_id    = get_the_ID();
					$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
					$idea_class = Functions\get_idea_class_with_votes($idea_id);
					?>
		
					<div class="wp-roadmap-idea flex flex-col justify-between border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden <?php echo esc_attr($idea_class); ?>" data-v0-t="card">
						<div class="p-6">
							<h2 class="text-2xl font-bold"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
		
							<p class="text-gray-500 mt-2 text-sm">Submitted on: <?php echo esc_html( get_the_date() ); ?></p>
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
		
							
							<p class="text-gray-700 mt-4 break-all">
								<?php
									echo wp_trim_words( get_the_excerpt(), 20 ) . ' <a class="text-blue-500 hover:underline" href="' . esc_url( get_permalink() ) . '" rel="ugc">read more...</a>';
								?>
							</p>


		
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
										<div class="text-white ml-2 idea-vote-count"><?php echo $vote_count; ?></div>
									</button>
								</div>
							</div>
						</div>
						<?php if ( current_user_can( 'administrator' ) ) : ?>
							<div class="p-6 bg-gray-200">
								<h6 class="text-center">Admin only</h6>
								<form class="idea-status-update-form" data-idea-id="<?php echo intval( $idea_id ); ?>">
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
				<?php endwhile; ?>
			</div>