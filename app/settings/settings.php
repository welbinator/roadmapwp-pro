<?php
function wp_roadmap_pro_save_settings() {
    if (isset($_POST['wp_roadmap_pro_settings'])) {
        check_admin_referer('wp_roadmap_pro_settings_action', 'wp_roadmap_pro_settings_nonce');

        $pro_settings = get_option('wp_roadmap_pro_settings', []);

        // Capture the 'hide_custom_idea_heading' checkbox value
        $pro_settings['hide_custom_idea_heading'] = isset($_POST['wp_roadmap_pro_settings']['hide_custom_idea_heading']) ? 1 : 0;

        // Capture the 'custom_idea_heading' text value
        $pro_settings['custom_idea_heading'] = sanitize_text_field($_POST['wp_roadmap_pro_settings']['custom_idea_heading']);

        update_option('wp_roadmap_pro_settings', $pro_settings);
    }
}
add_action('admin_init', 'wp_roadmap_pro_save_settings');