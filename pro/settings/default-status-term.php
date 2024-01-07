<?php
// Hook into admin initialization to register the setting
add_action('admin_init', 'wp_roadmap_pro_register_default_status_setting');

function wp_roadmap_pro_register_default_status_setting() {
    register_setting('wp_roadmap_pro_settings', 'wp_roadmap_pro_settings', 'wp_roadmap_pro_settings_validate');
}



