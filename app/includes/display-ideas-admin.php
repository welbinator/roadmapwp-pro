<?php
/**
 * Admin-only idea status update form include.
 *
 * @package RoadMapWP\Pro\Includes
 */

// Ensure $idea_id is defined when included
$idea_id = isset( $idea_id ) ? intval( $idea_id ) : 0;

if ( current_user_can( 'manage_options' ) ) : ?>
	<div class="p-6 bg-gray-200">
		<h6 class="text-center"><?php echo esc_html__( 'Admin only', 'roadmapwp-pro' ); ?></h6>
		<form class="rmwp__idea-status-update-form" data-idea-id="<?php echo intval( $idea_id ); ?>">
			<select multiple class="rmwp__status-select" name="idea_status[]">
				<?php
				$statuses = get_terms(
					array(
						'taxonomy'   => 'idea-status',
						'hide_empty' => false,
					)
				);

				if ( ! empty( $statuses ) && ! is_wp_error( $statuses ) ) {
					$current_statuses = wp_get_post_terms( $idea_id, 'idea-status', array( 'fields' => 'slugs' ) );
					if ( is_wp_error( $current_statuses ) ) {
						$current_statuses = array();
					}

					foreach ( $statuses as $status_term ) {
						$is_selected = in_array( $status_term->slug, $current_statuses, true );
						printf( '<option value="%s" %s>%s</option>', esc_attr( $status_term->slug ), selected( $is_selected, true, false ), esc_html( $status_term->name ) );
					}
				}
				?>
			</select>
			<button type="submit" class="block text-sm font-medium h-10 bg-gray-500 text-white px-4 py-2 rounded-lg update-status-button"><?php echo esc_html__( 'Update', 'roadmapwp-pro' ); ?></button>
		</form>
	</div>
<?php endif; ?>