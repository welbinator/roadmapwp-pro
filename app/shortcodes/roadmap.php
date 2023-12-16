<?php
/**
 * Shortcode to display the roadmap.
 *
 * @return string The HTML output for displaying the roadmap.
 */
function wp_roadmap_pro_roadmap_shortcode($atts) {
     // Flag to indicate the roadmap shortcode is loaded
     update_option('wp_roadmap_roadmap_shortcode_loaded', true);
     
    echo '<pre>' . var_export($atts, true) . '</pre>';
   // Parse the shortcode attributes
   $atts = shortcode_atts(array(
    'status' => '',
    'showNewIdea' => true,
    'showUpNext' => true,
    'showMaybe' => true,
    'showOnRoadmap' => true,
    'showClosed' => true,
), $atts, 'roadmap');

// Assume true if the attribute is not passed
$statuses = array();
    if (!empty($atts['status'])) {
        // Use the 'status' attribute if it's provided (for the shortcode)
        $statuses = array_map('trim', explode(',', $atts['status']));
    } else {
        // Otherwise, use the boolean attributes (for the block)
        if ($atts['showNewIdea']) $statuses[] = 'New Idea';
        if ($atts['showUpNext']) $statuses[] = 'Up Next';
        if ($atts['showMaybe']) $statuses[] = 'Maybe';
        if ($atts['showOnRoadmap']) $statuses[] = 'On Roadmap';
        if ($atts['showClosed']) $statuses[] = 'Closed';
    }

   

    // Retrieve color settings
    $pro_options = get_option('wp_roadmap_pro_settings');
    $vote_button_bg_color = isset($pro_options['vote_button_bg_color']) ? $pro_options['vote_button_bg_color'] : '#ff0000';
    $vote_button_text_color = isset($pro_options['vote_button_text_color']) ? $pro_options['vote_button_text_color'] : '#000000';
    $filter_tags_bg_color = isset($pro_options['filter_tags_bg_color']) ? $pro_options['filter_tags_bg_color'] : '#ff0000';
    $filter_tags_text_color = isset($pro_options['filter_tags_text_color']) ? $pro_options['filter_tags_text_color'] : '#000000';
    $filters_bg_color = isset($pro_options['filters_bg_color']) ? $pro_options['filters_bg_color'] : '#f5f5f5';

    $num_statuses = count($statuses);
    $md_cols_class = 'md:grid-cols-' . ($num_statuses > 3 ? 3 : $num_statuses); // Set to number of statuses, but max out at 4
    $lg_cols_class = 'lg:grid-cols-' . ($num_statuses > 4 ? 4 : $num_statuses);
    $xl_cols_class = 'xl:grid-cols-' . $num_statuses;
    ob_start(); // Start output buffering
    ?>
    <div class="roadmap_wrapper container mx-auto">
    <div class="roadmap-columns grid gap-4 <?php echo $md_cols_class; ?> <?php echo $lg_cols_class; ?> <?php echo $xl_cols_class; ?>">
            <?php
            foreach ($statuses as $status) {
                $args = array(
                    'post_type' => 'idea',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'status',
                            'field'    => 'name',
                            'terms'    => $status,
                        ),
                    ),
                );
                $query = new WP_Query($args);
                ?>
                <div class="roadmap-column">
                    <h3 style="text-align:center;"><?php echo esc_html__($status, 'wp-roadmap-pro'); ?></h3>
                    <?php
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
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
                            </div>
                            <?php
                        endwhile;
                    } else {
                        echo '<p>No ideas found for ' . esc_html($status) . '.</p>';
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
add_shortcode('roadmap', 'wp_roadmap_pro_roadmap_shortcode');
