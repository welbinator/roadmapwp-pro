<?php
/*
Plugin Name: RoadMapWP Pro
Plugin URI:  https://apexbranding.design/wp-roadmap
Description: Pro version of WP Roadmap, a roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     2.3.1
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: roadmapwp-pro
*/

// Define the current version of the plugin
define('RMWP_PLUGIN_VERSION', '2.3.1');

// Function to run on plugin activation
function roadmapwp_pro_activate() {
    // Check if the free version is active
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    if (is_plugin_active('roadmap-wp/wp-roadmap.php')) {
        // Deactivate the free version
        deactivate_plugins('roadmap-wp/wp-roadmap.php');
    }

    // Store the current version in the database
    update_option('rmwp_plugin_version', RMWP_PLUGIN_VERSION);

    // Register default idea taxonomies and add terms
    \RoadMapWP\Pro\CPT\register_default_idea_taxonomies();
    $status_terms = array('New Idea', 'Maybe', 'Up Next', 'On Roadmap', 'Not Now', 'Closed');
    foreach ($status_terms as $term) {
        if (!term_exists($term, 'idea-status')) {
            $result = wp_insert_term($term, 'idea-status');
            if (is_wp_error($result)) {
                error_log('Error inserting term ' . $term . ': ' . $result->get_error_message());
            }
        }
    }

    // Create pages
    create_pages();

    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'roadmapwp_pro_activate');

// Function to check version and flush permalinks if updated
function roadmapwp_pro_check_version() {
    // Get the stored version
    $stored_version = get_option('rmwp_plugin_version');

    // Check if the current version is different from the stored version
    if ($stored_version !== RMWP_PLUGIN_VERSION) {
        // Update the stored version
        update_option('rmwp_plugin_version', RMWP_PLUGIN_VERSION);

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
add_action('admin_init', 'roadmapwp_pro_check_version');

// Function to display admin notices
function rmwp_pro_admin_notices() {
    if (isset($_GET['sl_activation']) && !empty($_GET['message'])) {
        switch ($_GET['sl_activation']) {
            case 'false':
                $message = urldecode($_GET['message']);
                ?>
                <div class="error">
                    <p><?php echo wp_kses_post($message); ?></p>
                </div>
                <?php
                break;
            case 'true':
            default:
                // Optional success message
                break;
        }
    }
}
add_action('admin_notices', 'rmwp_pro_admin_notices');

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'pro/settings/settings.php';
require_once plugin_dir_path(__FILE__) . 'pro/settings/custom-taxonomies.php';
require_once plugin_dir_path(__FILE__) . 'pro/settings/choose-idea-template.php';
require_once plugin_dir_path(__FILE__) . 'pro/blocks/blocks.php';
require_once plugin_dir_path(__FILE__) . 'pro/blocks/single-idea-block.php';
require_once plugin_dir_path(__FILE__) . 'pro/blocks/roadmap-block.php';
require_once plugin_dir_path(__FILE__) . 'pro/blocks/roadmap-tabs-block.php';
require_once plugin_dir_path(__FILE__) . 'pro/blocks/new-idea-form-block.php';
require_once plugin_dir_path(__FILE__) . 'pro/blocks/display-ideas-block.php';
require_once plugin_dir_path(__FILE__) . 'app/settings/submit-idea-custom-heading.php';
require_once plugin_dir_path(__FILE__) . 'app/settings/display-ideas-custom-heading.php';
require_once plugin_dir_path(__FILE__) . 'app/customizer-styles.php';
require_once plugin_dir_path(__FILE__) . 'app/admin-pages.php';
require_once plugin_dir_path(__FILE__) . 'app/admin-functions.php';
require_once plugin_dir_path(__FILE__) . 'app/cpt-ideas.php';
require_once plugin_dir_path(__FILE__) . 'app/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/new-idea-form.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/display-ideas.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/roadmap.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/roadmap-tabs.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/single-idea.php';
require_once plugin_dir_path(__FILE__) . 'app/class-voting.php';

if (file_exists(plugin_dir_path(__FILE__) . 'gutenberg-market.php')) {
    include_once plugin_dir_path(__FILE__) . 'gutenberg-market.php';
}

// Handle custom template for single ideas
function rmwp_pro_custom_template($template) {
    global $post;

    if ('idea' === $post->post_type) {
        $options = get_option('wp_roadmap_settings');
        $chosen_idea_template = isset($options['single_idea_template']) ? $options['single_idea_template'] : 'plugin';

        if ($chosen_idea_template === 'plugin' && file_exists(plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php')) {
            return plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php';
        }
    }

    return $template;
}
add_filter('single_template', 'rmwp_pro_custom_template');

// Log all status terms
function rmwp_pro_log_all_status_terms() {
    get_terms(array(
        'taxonomy' => 'idea-status',
        'hide_empty' => false,
    ));
}
add_action('init', 'rmwp_pro_log_all_status_terms');

// Create pages on activation
function create_pages() {
    // Define the pages and their corresponding details
    $pages = array(
        array(
            'title' => 'Submit an Idea',
            'content' => '[new_idea_form]' . "\n\n" . '[display_ideas]',
            'status' => 'publish'
        ),
        array(
            'title' => 'Roadmap',
            'content' => '[roadmap status="Closed, Up Next, On Roadmap"]',
            'status' => 'publish'
        ),
        array(
            'title' => 'Roadmap Tabs',
            'content' => '[roadmap_tabs status="Closed, Up Next, On Roadmap"]',
            'status' => 'draft'
        )
    );

    foreach ($pages as $page) {
        // Check if the page already exists
        $page_exists = get_page_by_title($page['title']);

        // If the page does not exist, create it
        if (!$page_exists) {
            $new_page = array(
                'post_title'    => $page['title'],
                'post_content'  => $page['content'],
                'post_status'   => $page['status'], // Set the status (publish or draft)
                'post_author'   => 1, // Make sure to set the correct author ID
                'post_type'     => 'page',
                'post_name'     => sanitize_title($page['title'])
            );

            // Insert the page into the database
            $new_page_id = wp_insert_post($new_page);

            // Optional: Set a meta flag to indicate that your plugin created this page
            if ($new_page_id && !is_wp_error($new_page_id)) {
                update_post_meta($new_page_id, '_created_by_my_plugin', true);
            }
        }
    }
}
