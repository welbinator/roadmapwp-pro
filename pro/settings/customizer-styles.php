<?php

// namespace RoadMapWP\Pro\Settings\CustomizerStyles;

// add_action( 'customize_register', __NAMESPACE__ . '\\register_customizer_styles' );
add_action( 'customize_register', 'rmwp_register_customizer_styles' );

/**
 * Register customizer settings for Roadmap styles.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager object.
 */
function rmwp_register_customizer_styles( $wp_customize ) {
    // Add Roadmap Styles panel to customizer.
    $wp_customize->add_panel(
        'roadmap_styles',
        array(
            'title'       => __( 'Roadmap Styles', 'roadmapwp-pro' ),
            'description' => __( 'Customize Roadmap Styles', 'roadmapwp-pro' ),
            'priority'    => 160,
        )
    );

    // Add a minimal section for the color control
    $wp_customize->add_section(
        'roadmap_global_styles',
        array(
            'title'    => __( 'Idea Styles', 'roadmapwp-pro' ),
            'panel'    => 'roadmap_styles',
            'priority' => 1,
        )
    );

    // Add a minimal section for the color control
    $wp_customize->add_section(
        'roadmap_tabs_styles',
        array(
            'title'    => __( 'Roadmap Tabs Styles', 'roadmapwp-pro' ),
            'panel'    => 'roadmap_styles',
            'priority' => 1,
        )
    );

    /*
    * Global Styles
    */

    // Vote Button Background Color.
    $wp_customize->add_setting(
        'vote_button_background_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'vote_button_background_color',
            array(
                'label'    => __( 'Vote Button Background Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_global_styles',
                'settings' => 'vote_button_background_color',
            )
        )
    );

    // Vote Button Text Color.
    $wp_customize->add_setting(
        'vote_button_text_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'vote_button_text_color',
            array(
                'label'    => __( 'Vote Button Text Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_global_styles',
                'settings' => 'vote_button_text_color',
            )
        )
    );

    // Tags Background Color
    $wp_customize->add_setting(
        'tags_background_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'tags_background_color',
            array(
                'label'    => __( 'Tags Background Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_global_styles',
                'settings' => 'tags_background_color',
            )
        )
    );

    // Tags Text Color
    $wp_customize->add_setting(
        'tags_text_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'tags_text_color',
            array(
                'label'    => __( 'Tags Text Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_global_styles',
                'settings' => 'tags_text_color',
            )
        )
    );

     // Submit Idea Button Background Color
     $wp_customize->add_setting(
        'submit_idea_button_background_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'submit_idea_button_background_color',
            array(
                'label'    => __( 'Submit Idea Button Background Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_global_styles',
                'settings' => 'submit_idea_button_background_color',
            )
        )
    );

    // Submit Idea Button Text Color
    $wp_customize->add_setting(
        'submit_idea_button_text_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'submit_idea_button_text_color',
            array(
                'label'    => __( 'Submit Idea Button Text Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_global_styles',
                'settings' => 'submit_idea_button_text_color',
            )
        )
    );

     // Filter Box Background Color
     $wp_customize->add_setting(
        'filter_box_background_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'filter_box_background_color',
            array(
                'label'    => __( 'Filter Box Background Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_global_styles',
                'settings' => 'filter_box_background_color',
            )
        )
    );

    // Filter Box Text Color
    $wp_customize->add_setting(
        'filter_box_text_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'filter_box_text_color',
            array(
                'label'    => __( 'Filter Box Text Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_global_styles',
                'settings' => 'filter_box_text_color',
            )
        )
    );

    /*
    * Roadmap Tabs Styles
    */

    // Tabs Container Background Color
    $wp_customize->add_setting(
        'tabs_container_background_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'tabs_container_background_color',
            array(
                'label'    => __( 'Container Background Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_tabs_styles',
                'settings' => 'tabs_container_background_color',
            )
        )
    );

    // Roadmap Tabs Tab Background Color
    $wp_customize->add_setting(
        'tabs_tab_background_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'tabs_tab_background_color',
            array(
                'label'    => __( 'Tab Background Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_tabs_styles',
                'settings' => 'tabs_tab_background_color',
            )
        )
    );

    // Roadmap Tabs Tab Text Color
    $wp_customize->add_setting(
        'tabs_tab_text_color',
        array(
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new \WP_Customize_Color_Control(
            $wp_customize,
            'tabs_tab_text_color',
            array(
                'label'    => __( 'Tab Text Color', 'roadmapwp-pro' ),
                'section'  => 'roadmap_tabs_styles',
                'settings' => 'tabs_tab_text_color',
            )
        )
    );

}

add_action( 'wp_enqueue_scripts', 'custom_styles' );

/**
 * Enqueue custom styles for LearnDash.
 */
function custom_styles() {
	wp_enqueue_style(
		'rmwp-custom-styles',
		plugin_dir_url( __FILE__ ) . '../../app/assets/css/customizer-styles.css',
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . '../../app/assets/css/customizer-styles.css' )
	);

	/*
	* Global styles
	*/
	$custom_css = '
	.idea-vote-button, .idea-vote-count { 
        background-color: ' . get_theme_mod( 'vote_button_background_color', '' ) . '!important;
		color: ' . get_theme_mod( 'vote_button_text_color', '' ) . '!important;
    }
    .idea-tags a { 
        background-color: ' . get_theme_mod( 'tags_background_color', '' ) . '!important;
		color: ' . get_theme_mod( 'tags_text_color', '' ) . '!important;
    }
    .new_idea_form_input input[type="submit"] { 
        background-color: ' . get_theme_mod( 'submit_idea_button_background_color', '' ) . '!important;
		color: ' . get_theme_mod( 'submit_idea_button_text_color', '' ) . '!important;
    }
    .filters-wrapper { 
        background-color: ' . get_theme_mod( 'filter_box_background_color', '' ) . '!important;
    }
    .filters-wrapper * { 
		color: ' . get_theme_mod( 'filter_box_text_color', '' ) . '!important;
    }
	';

    /*
    * Roadmap Tabs Styles
    */

    $custom_css .= '
    .roadmap-tabs-wrapper .roadmap-tabs { 
		background-color: ' . get_theme_mod( 'tabs_container_background_color', '' ) . '!important;
    }
    .roadmap-tabs-wrapper .roadmap-tabs .roadmap-tab { 
		background-color: ' . get_theme_mod( 'tabs_tab_background_color', '' ) . '!important;
        color: ' . get_theme_mod( 'tabs_tab_text_color', '' ) . '!important;
    }
    ';

    wp_add_inline_style( 'rmwp-custom-styles', $custom_css );
}