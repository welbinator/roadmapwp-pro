<?php
/**
 * Function to display WP RoadMap settings page.
 */
function wp_roadmap_pro_settings_page() {
    // Fetch current settings
    $pro_options = get_option('wp_roadmap_pro_settings', array('default_status_term' => 'new-idea'));
    $status_terms = get_terms(array('taxonomy' => 'status', 'hide_empty' => false));
    $selected_page = isset($pro_options['single_idea_page']) ? $pro_options['single_idea_page'] : '';
    $default_status_term = isset($pro_options['default_status_term']) ? $pro_options['default_status_term'] : 'new-idea';

    // Log form submission data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('Form submitted with data: ' . print_r($_POST, true));
    }

   
    
    
    // New Styling Section
    $vote_button_bg_color = isset($pro_options['vote_button_bg_color']) ? $pro_options['vote_button_bg_color'] : '#0000ff'; // Default to blue if not set
    $vote_button_text_color = isset($pro_options['vote_button_text_color']) ? $pro_options['vote_button_text_color'] : '#000000'; // Default to blue if not set
    $filter_tags_bg_color = isset($pro_options['filter_tags_bg_color']) ? $pro_options['filter_tags_bg_color'] : '#0000ff'; // Default to blue if not set
    $filter_tags_text_color = isset($pro_options['filter_tags_text_color']) ? $pro_options['filter_tags_text_color'] : '#000000'; // Default to blue if not set
    $filters_bg_color = isset($pro_options['filters_bg_color']) ? $pro_options['filters_bg_color'] : '#f5f5f5'; // Default to blue if not set
    $tabs_container_bg_color = isset($pro_options['tabs_container_bg_color']) ? $pro_options['tabs_container_bg_color'] : '#dddddd'; // Default to blue if not set
    $tabs_button_bg_color = isset($pro_options['tabs_button_bg_color']) ? $pro_options['tabs_button_bg_color'] : '#ffffff'; // Default to blue if not set
    $tabs_text_color = isset($pro_options['tabs_text_color']) ? $pro_options['tabs_text_color'] : '#000000'; // Default to blue if not set
    
   
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
        <?php
        settings_fields('wp_roadmap_pro_settings');
        do_settings_sections('wp_roadmap_pro_settings');
        wp_nonce_field('wp_roadmap_pro_settings_action', 'wp_roadmap_pro_settings_nonce');
        ?>
            <?php
            settings_fields('wp_roadmap_pro_settings');
            do_settings_sections('wp_roadmap_pro_settings');
            ?>

            <table class="form-table">
                                

            <tr valign="top">
                <th scope="row"><?php esc_html_e('Set Default Status Term for New Ideas', 'wp-roadmap-pro'); ?></th>
                <td>
                    <select name="wp_roadmap_pro_settings[default_status_term]">
                        <?php foreach ($status_terms as $term) : ?>
                            <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($default_status_term, $term->slug); ?>>
                                <?php echo esc_html($term->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
                

                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Set Published/Pending/Draft', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_default_idea_status_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap-pro') . '</a>');
                        ?>
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Single Idea Template', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <?php
                        // This filter will be handled in choose-idea-template.php
                        echo apply_filters('wp_roadmap_single_idea_template_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap-pro') . '</a>');
                        ?>
                    </td>
                </tr>


                

                <?php $displayCommentsStyle = $pro_options['single_idea_template'] === 'plugin' ? '' : 'style="display: none;"'; ?>
                
                <tr id="allow-comments-setting" valign="top" <?php echo $displayCommentsStyle; ?>>
                    <th scope="row"><?php esc_html_e('Allow Comments on Ideas', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <?php
                        // Apply the filter here
                        echo apply_filters('wp_roadmap_enable_comments_setting', '');
                        ?>
                    </td>
                </tr>
                

                <!-- Hide New Idea Heading Setting -->
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Custom "Submit Idea" Heading', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_hide_custom_idea_heading_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap-pro') . '</a>');
                        ?>
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Custom "Browse Ideas" Heading', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_hide_display_ideas_heading_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap-pro') . '</a>');
                        ?>
                    </td>
                </tr>

           
                <tr>
                    <td style="padding:0;padding-block:20px;">
                        <hr>
                        <h1>Styling</h1>
                        <hr>
                    </td>
                </tr>


                <tr>
                    <td style="padding:0;padding-block:20px;">
                        <h4 style="margin: 0px;">Vote Button</h4>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Vote Button Background Color', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <input type="text" name="wp_roadmap_pro_settings[vote_button_bg_color]" value="<?php echo esc_attr($vote_button_bg_color); ?>" class="wp-roadmap-color-picker"/>
                        <!-- <button type="button" class="wp-roadmap-reset-color" data-default-color="#0000ff">Reset</button> -->
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Vote Button Text Color', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <input type="text" name="wp_roadmap_pro_settings[vote_button_text_color]" value="<?php echo esc_attr($vote_button_text_color); ?>" class="wp-roadmap-color-picker"/>
                       
                    </td>
                    
                </tr>
                <tr><td><hr></td></tr>
                <tr>
                    <td style="padding:0;padding-block:20px;">
                        <h4 style="margin: 0px;">Filters</h4>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Filter Tags Background Color', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <input type="text" name="wp_roadmap_pro_settings[filter_tags_bg_color]" value="<?php echo esc_attr($filter_tags_bg_color); ?>" class="wp-roadmap-color-picker"/>
                        <!-- <button type="button" class="wp-roadmap-reset-color" data-default-color="#0000ff">Reset</button> -->
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Filter Tags Text Color', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <input type="text" name="wp_roadmap_pro_settings[filter_tags_text_color]" value="<?php echo esc_attr($filter_tags_text_color); ?>" class="wp-roadmap-color-picker"/>
                        <!-- <button type="button" class="wp-roadmap-reset-color" data-default-color="#0000ff">Reset</button> -->
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Filters Background Color', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <input type="text" name="wp_roadmap_pro_settings[filters_bg_color]" value="<?php echo esc_attr($filters_bg_color); ?>" class="wp-roadmap-color-picker"/>
                        <!-- <button type="button" class="wp-roadmap-reset-color" data-default-color="#0000ff">Reset</button> -->
                    </td>
                </tr>
                <tr><td><hr></td></tr>
                <tr>
                    <td style="padding:0;padding-block:20px;">
                        <h4 style="margin: 0px;">Roadmap Tabs</h4>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Roadmap Tabs Container Background Color', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <input type="text" name="wp_roadmap_pro_settings[tabs_container_bg_color]" value="<?php echo esc_attr($tabs_container_bg_color); ?>" class="wp-roadmap-color-picker"/>
                        <!-- <button type="button" class="wp-roadmap-reset-color" data-default-color="#0000ff">Reset</button> -->
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Roadmap Tabs Background Color', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <input type="text" name="wp_roadmap_pro_settings[tabs_button_bg_color]" value="<?php echo esc_attr($tabs_button_bg_color); ?>" class="wp-roadmap-color-picker"/>
                        <!-- <button type="button" class="wp-roadmap-reset-color" data-default-color="#0000ff">Reset</button> -->
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Roadmap Tabs Text Color', 'wp-roadmap-pro'); ?></th>
                    <td>
                        <input type="text" name="wp_roadmap_pro_settings[tabs_text_color]" value="<?php echo esc_attr($tabs_text_color); ?>" class="wp-roadmap-color-picker"/>
                        <!-- <button type="button" class="wp-roadmap-reset-color" data-default-color="#0000ff">Reset</button> -->
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
    // Enqueue the color picker JavaScript and styles
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');

    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Initialize color picker
        $('.wp-roadmap-color-picker').wpColorPicker();
    });
    </script>
    <?php
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Full POST Data: ' . print_r($_POST, true));
    error_log('Form submitted with data: ' . print_r($_POST, true));

    // Extracting the settings from the POST data for debugging purposes
    $posted_settings = $_POST['wp_roadmap_pro_settings'] ?? [];
    error_log('POST data (wp_roadmap_pro_settings): ' . print_r($posted_settings, true));

    // Optionally, confirm the current settings from the database before any update
    $current_settings = get_option('wp_roadmap_pro_settings');
    error_log('Current settings from database before update: ' . print_r($current_settings, true));
}



/**
 * Function to display the Taxonomies management page.
 * This function allows adding terms to the "Tags" taxonomy.
 */
function wp_roadmap_pro_taxonomies_page() {
    // Check if the current user has the 'manage_options' capability
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wp-roadmap-pro'));
    }
   
    $pro_feature = apply_filters('wp_roadmap_pro_add_taxonomy_feature', '');

    echo '<h2>Taxonomies</h2>';


    echo $pro_feature;
       
}

function wp_roadmap_pro_help_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <hr />
        <h2 style="font-size: 1.5em;"><a href="https://roadmapwp.com/kb_category/shortcodes/" target="_blank">Shortcodes</a></h2>
        <hr />
        <ul>
            <li style="font-size: 1.1em;"><strong><a href="https://roadmapwp.com/kb_article/new-idea-form-shortcode/" target="_blank">New Idea Form:</a></strong><span> [new_idea_form]</span></li>
            <li style="font-size: 1.1em;"><strong><a href="https://roadmapwp.com/kb_article/display-ideas-shortcode/" target="_blank">Display Ideas:</a></strong><span> [display_ideas]</span></li>
            <li style="font-size: 1.1em;"><strong><a href="https://roadmapwp.com/kb_article/roadmap-shortcode/" target=_blank">Roadmap:</a></strong><span> [roadmap status=""]</span></li>
            <li style="font-size: 1.1em;">Use "status" parameter to choose which status or statuses to display Example: [roadmap status="Up Next, On Roadmap"]</li>
            <li style="font-size: 1.1em;">Possible values for status parameter (these are the default options but can be changed in Taxonomies page):</li>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>New Idea</li>
                    <li>Not Now</li>
                    <li>Maybe</li>
                    <li>Up Next</li>
                    <li>On Roadmap</li>
                    <li>Closed</li>
                </ul>
            <li style="font-size: 1.1em;"><strong><a href="https://roadmapwp.com/kb_article/roadmap-with-tabs-shortcode/" target="_blank">Roadmap Tabs:</a></strong><span> [roadmap_tabs status=""]</span></li>
            <li style="font-size: 1.1em;">Use "status" parameter to choose which status or statuses to display Example: [roadmap_tabs status="Up Next, On Roadmap"]</li>
            <li style="font-size: 1.1em;">Possible values for status parameter (these are the default options but can be changed in Taxonomies page):</li>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>New Idea</li>
                    <li>Not Now</li>
                    <li>Maybe</li>
                    <li>Up Next</li>
                    <li>On Roadmap</li>
                    <li>Closed</li>
                </ul>
        </ul>
        <hr />
        <h2 style="font-size: 1.5em;"><a href="https://roadmapwp.com/kb_category/blocks/" target="_blank">Blocks</a></h2>
        <hr />
        <ul>
            <li style="font-size: 1.1em;"><strong><a href="https://roadmapwp.com/kb_article/new-idea-form-block/" target="_target">New Idea Form</a></strong></li>
            <li style="font-size: 1.1em;"><strong><a href="https://roadmapwp.com/kb_article/display-ideas-block/" target="_blank">Display Ideas</a></strong></li>
            <li style="font-size: 1.1em;"><strong><a href="https://roadmapwp.com/kb_article/roadmap-block/" target="_blank">Roadmap</a></strong></li>
            <li style="font-size: 1.1em;">After adding the block to the page, in the block editor choose which statuses you want to display.<li>
            <li style="font-size: 1.1em;">Available statuses (these are the default options but can be changed in Taxonomies page):</li>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>New Idea</li>
                    <li>Not Now</li>
                    <li>Maybe</li>
                    <li>Up Next</li>
                    <li>On Roadmap</li>
                    <li>Closed</li>
                </ul>
            <li style="font-size: 1.1em;"><strong><a href="https://roadmapwp.com/kb_article/roadmap-tabs-block/" target="_blank">Roadmap Tabs</a></strong></li>
            <li style="font-size: 1.1em;">After adding the block to the page, in the block editor choose which statuses you want to display.</li>
            <li style="font-size: 1.1em;">Available statuses (these are the default options but can be changed in Taxonomies page):</li>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>New Idea</li>
                    <li>Not Now</li>
                    <li>Maybe</li>
                    <li>Up Next</li>
                    <li>On Roadmap</li>
                    <li>Closed</li>
                </ul>
        </ul>
        <!-- Add more content or instructions here as needed -->
    </div>
    <style>
        li {
            margin: 10px;
        }
        </style>
    <?php
}



