<?php
function wp_roadmap_pro_register_blocks() {
    // Block Editor Script
    wp_register_script(
        'wp-roadmap-pro-blocks',
        plugin_dir_url(__FILE__) . 'blocks.js', // Path to your block's JS file
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor')
    );

    // Register each block
    $blocks = array(
        'new-idea-form' => 'wp_roadmap_pro_new_idea_form_shortcode',
        'display-ideas' => 'wp_roadmap_pro_display_ideas_shortcode',
        'single-idea' => 'wp_roadmap_pro_single_idea_shortcode',
    );

    foreach ($blocks as $block_name => $callback) {
        register_block_type('wp-roadmap-pro/' . $block_name, array(
            'editor_script' => 'wp-roadmap-pro-blocks',
            'render_callback' => $callback,
        ));
    }
}

add_action('init', 'wp_roadmap_pro_register_blocks');


function wp_roadmap_pro_enqueue_block_editor_assets() {
    if (function_exists('get_current_screen')) {
        $screen = get_current_screen();
        if ($screen && $screen->is_block_editor()) {
            // Enqueue the existing script
            wp_enqueue_script('wp-roadmap-pro-blocks');

            // Enqueue the Roadmap block editor script
            wp_enqueue_script(
                'wp-roadmap-pro-roadmap-block',
                plugin_dir_url(__FILE__) . 'build/roadmap-block.js',
                array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
                filemtime(plugin_dir_path(__FILE__) . 'build/roadmap-block.js')
            );

            // Enqueue the Roadmap Tabs block editor script
            wp_enqueue_script(
                'wp-roadmap-pro-roadmap-tabs-block',
                plugin_dir_url(__FILE__) . 'build/roadmap-tabs-block.js',
                array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
                filemtime(plugin_dir_path(__FILE__) . 'build/roadmap-tabs-block.js')
            );
        }
    }
}

add_action('enqueue_block_editor_assets', 'wp_roadmap_pro_enqueue_block_editor_assets');
