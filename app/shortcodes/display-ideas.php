<?php 
/**
 * Shortcode to display ideas.
 *
 * @return string The HTML output for displaying ideas.
 */
function wp_roadmap_pro_display_ideas_shortcode() {
   // Flag to indicate the display ideas shortcode is loaded
   update_option('wp_roadmap_ideas_shortcode_loaded', true);

    ob_start(); // Start output buffering

   

    // Always include 'idea-tag' taxonomy
    $taxonomies = array('idea-tag');

    // Include custom taxonomies 
    
        $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
        $taxonomies = array_merge($taxonomies, array_keys($custom_taxonomies));
    

    // Exclude 'status' taxonomy
    $exclude_taxonomies = array('status');
    $taxonomies = array_diff($taxonomies, $exclude_taxonomies);

    // Retrieve color settings
    $pro_options = get_option('wp_roadmap_pro_settings');
    $vote_button_bg_color = isset($pro_options['vote_button_bg_color']) ? $pro_options['vote_button_bg_color'] : '#ff0000';
    $vote_button_text_color = isset($pro_options['vote_button_text_color']) ? $pro_options['vote_button_text_color'] : '#000000';
    $filter_tags_bg_color = isset($pro_options['filter_tags_bg_color']) ? $pro_options['filter_tags_bg_color'] : '#ff0000';
    $filter_tags_text_color = isset($pro_options['filter_tags_text_color']) ? $pro_options['filter_tags_text_color'] : '#000000';
    $filters_bg_color = isset($pro_options['filters_bg_color']) ? $pro_options['filters_bg_color'] : '#f5f5f5';

    // Check if the pro version is installed and settings are enabled
    $hide_display_ideas_heading = apply_filters('wp_roadmap_hide_display_ideas_heading', false);
    $new_display_ideas_heading = apply_filters('wp_roadmap_custom_display_ideas_heading_text', 'Browse Ideas');
?>
    
    <div class="roadmap_wrapper container mx-auto">
    <div class="browse_ideas_frontend">
        <?php
    $output = '<h2>' . esc_html($new_display_ideas_heading) . '</h2>';
        if (!$hide_display_ideas_heading) {
            echo $output; 
        }
        ?>
        <div class="filters-wrapper" style="background-color: <?php echo esc_attr($filters_bg_color); ?>;">
            <h4>Filters:</h4>
            <div class="filters-inner">
                <?php foreach ($taxonomies as $taxonomy_slug) : 
                    $taxonomy = get_taxonomy($taxonomy_slug);
                    if ($taxonomy && $taxonomy_slug != 'status') : ?>
                        <div class="wp-roadmap-ideas-filter-taxonomy" data-taxonomy="<?php echo esc_attr($taxonomy_slug); ?>">
                            <label><?php echo esc_html($taxonomy->labels->singular_name); ?>:</label>
                            <div class="taxonomy-term-labels">
                                <?php
                                $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));
                                foreach ($terms as $term) {
                                    echo '<label class="taxonomy-term-label">';
                                    echo '<input type="checkbox" name="idea_taxonomies[' . esc_attr($taxonomy->name) . '][]" value="' . esc_attr($term->slug) . '"> ';
                                    echo esc_html($term->name);
                                    echo '</label>';
                                }
                                ?>
                            </div>
                            <div class="filter-match-type">
                                <label><input type="radio" name="match_type_<?php echo esc_attr($taxonomy->name); ?>" value="any" checked> Any</label>
                                <label><input type="radio" name="match_type_<?php echo esc_attr($taxonomy->name); ?>" value="all"> All</label>
                            </div>
                        </div>
                    <?php endif; 
                endforeach; ?>
            </div>
        </div>
        </div>

        <div class="wp-roadmap-ideas-list">

        <?php
        $args = array(
            'post_type' => 'idea',
            'posts_per_page' => -1 // Adjust as needed
        );
        $query = new WP_Query($args);

        if ($query->have_posts()) : ?>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">
                <?php while ($query->have_posts()) : $query->the_post();
                    $idea_id = get_the_ID();
                    $vote_count = get_post_meta($idea_id, 'idea_votes', true) ?: '0'; ?>
        
                    <div class="wp-roadmap-idea flex flex-col justify-between border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden" data-v0-t="card">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold"><a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a></h2>
        
                            <p class="text-gray-500 mt-2 text-sm">Submitted on: <?php echo get_the_date(); ?></p>
                            <div class="flex flex-wrap space-x-2 mt-2">
                                <?php $terms = wp_get_post_terms($idea_id, $taxonomies);
                                foreach ($terms as $term) :
                                    $term_link = get_term_link($term);
                                    if (!is_wp_error($term_link)) : ?>
                                        <a href="<?php echo esc_url($term_link); ?>" class="inline-flex items-center border font-semibold bg-blue-500 px-3 py-1 rounded-full text-sm" style="background-color: <?php echo esc_attr($filter_tags_bg_color); ?>;color: <?php echo esc_attr($filter_tags_text_color); ?>;"><?php echo esc_html($term->name); ?></a>
                                    <?php endif;
                                endforeach; ?>
                            </div>
        
                            
                            <p class="text-gray-700 mt-4"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>

        
                            <div class="flex items-center justify-between mt-6">
                                <a class="text-blue-500 hover:underline" href="<?php echo esc_url(get_permalink()); ?>" rel="ugc">Read More</a>
                                <div class="flex items-center idea-vote-box" data-idea-id="<?php echo $idea_id; ?>">
                                    <button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 px-4 py-2 rounded-lg idea-vote-button" style="background-color: <?php echo esc_attr($vote_button_bg_color); ?>!important;background-image: none!important;color: <?php echo esc_attr($vote_button_text_color); ?>!important;">
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
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php else : ?>
        <p>No ideas found.</p>
    <?php endif; 
    

    wp_reset_postdata();

    return ob_get_clean(); // Return the buffered output
}

add_shortcode('display_ideas', 'wp_roadmap_pro_display_ideas_shortcode');