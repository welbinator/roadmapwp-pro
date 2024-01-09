<?php
// Include in your plugin's main file or in the functions.php of your theme.

function wp_roadmap_pro_register_roadmap_block() {
    // Register the block script
    wp_register_script(
        'wp-roadmap-pro-roadmap-block',
        plugin_dir_url(__FILE__) . '../../build/roadmap-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-api-fetch')
    );
    

    // Register the block
    register_block_type('wp-roadmap-pro/roadmap-block', array(
        'editor_script' => 'wp-roadmap-pro-roadmap-block',
        'render_callback' => 'wp_roadmap_pro_roadmap_block_render',
    ));
}

add_action('init', 'wp_roadmap_pro_register_roadmap_block');

// The render callback function for the block
function wp_roadmap_pro_roadmap_block_render($attributes) {
    // Check if selectedStatuses attribute is set and is an array
    if (isset($attributes['selectedStatuses']) && is_array($attributes['selectedStatuses'])) {
        $selected_statuses = array_keys(array_filter($attributes['selectedStatuses']));
    } else {
        // If no statuses are selected, you can choose to return nothing or handle it differently
        return '<p>No statuses selected.</p>';
    }

    // Check the status filter attribute
    $include_pending = isset($attributes['statusFilter']) && $attributes['statusFilter'] === 'include_pending';

    $num_statuses = count($selected_statuses);
    $md_cols_class = 'md:grid-cols-' . ($num_statuses > 3 ? 3 : $num_statuses); // Set to number of statuses, but max out at 3
    $lg_cols_class = 'lg:grid-cols-' . ($num_statuses > 4 ? 4 : $num_statuses);
    $xl_cols_class = 'xl:grid-cols-' . $num_statuses;
    ob_start(); ?>

    <div class="roadmap_wrapper container mx-auto">
        <div class="roadmap-columns grid gap-4 <?php echo $md_cols_class; ?> <?php echo $lg_cols_class; ?> <?php echo $xl_cols_class; ?>">
            <?php foreach ($selected_statuses as $status_slug) :
                $term = get_term_by('slug', $status_slug, 'status');
                if (!$term) {
                    continue; // Skip if term not found
                }

                $args = array(
                    'post_type' => 'idea',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                        'taxonomy' => 'status',
                        'field'    => 'slug',
                        'terms'    => $status_slug,
                        ),
                    ),
                );

                // Include pending review ideas if selected
                if ($include_pending) {
                    $args['post_status'] = array('publish', 'pending');
                }

                $query = new WP_Query($args); ?>

                <div class="roadmap-column">
                    <h3 style="text-align:center;"><?php echo esc_html($term->name); ?></h3>
                    <?php if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                        $idea_id = get_the_ID();
                            // Check post status if including pending reviews
                            if (!$include_pending && get_post_status() !== 'publish') {
                                continue;
                            }
                            ?>
                            <div class="border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden m-2 wp-roadmap-idea">
                                <div class="p-6">
                                    <h4 class="idea-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h4>
                                    <p class="text-gray-500 mt-2 mb-0 text-sm"><?php echo get_the_date(); ?></p>
                                    <div class="flex flex-wrap space-x-2 mt-2 idea-tags">
                                        <!-- Display tags or other taxonomies -->
                                    </div>
                                    <p class="idea-excerpt"><?php the_excerpt(); ?></p>
                                    <a class="text-blue-500 hover:underline" href="<?php the_permalink(); ?>" rel="ugc">Read More</a>
                                </div>
                                <?php if (current_user_can('administrator')): ?>
                                    <div class="p-6 bg-gray-200">
                                        <h6 class="text-center">Admin only</h6>
                                        <form class="idea-status-update-form" data-idea-id="<?php echo $idea_id; ?>">
                                            <select multiple class="status-select" name="idea_status[]">
                                                <?php 
                                                $statuses = get_terms('status', array('hide_empty' => false));
                                                $current_statuses = wp_get_post_terms($idea_id, 'status', array('fields' => 'slugs'));
                                                
                                                foreach ($statuses as $status) {
                                                    $selected = in_array($status->slug, $current_statuses) ? 'selected' : '';
                                                    echo '<option value="' . esc_attr($status->slug) . '" ' . $selected . '>' . esc_html($status->name) . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" class="block text-sm font-medium h-10 bg-gray-500 text-white px-4 py-2 rounded-lg update-status-button">Update</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile;
                        else : ?>
                        <p>No ideas found for <?php echo esc_html($term->name); ?>.</p>
                    <?php endif;
                    wp_reset_postdata(); ?>
                </div> <!-- Close column -->
            <?php endforeach; ?>
        </div> <!-- Close grid -->
    </div>

        <?php return ob_get_clean();
}


