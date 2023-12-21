<?php
function wp_roadmap_pro_save_settings() {
    if (isset($_POST['wp_roadmap_pro_settings']) && is_array($_POST['wp_roadmap_pro_settings'])) {
        check_admin_referer('wp_roadmap_pro_settings_action', 'wp_roadmap_pro_settings_nonce');

        // Ensure $pro_settings is an array
        $pro_settings = get_option('wp_roadmap_pro_settings', []);
        if (!is_array($pro_settings)) {
            $pro_settings = []; // Initialize as an empty array if not an array
        }

        // Check and set 'hide_custom_idea_heading'
        $pro_settings['hide_custom_idea_heading'] = isset($_POST['wp_roadmap_pro_settings']['hide_custom_idea_heading']) ? 1 : 0;

        // Check and set 'custom_idea_heading'
        if (isset($_POST['wp_roadmap_pro_settings']['custom_idea_heading'])) {
            $pro_settings['custom_idea_heading'] = sanitize_text_field($_POST['wp_roadmap_pro_settings']['custom_idea_heading']);
        }

        update_option('wp_roadmap_pro_settings', $pro_settings);
    }
}
add_action('admin_init', 'wp_roadmap_pro_save_settings');

