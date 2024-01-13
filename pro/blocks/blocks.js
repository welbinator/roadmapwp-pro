(function() {
    const { registerBlockType } = wp.blocks;
    const { createElement } = wp.element;
    const { InspectorControls, CheckboxControl } = wp.blockEditor;
    const { __ } = wp.i18n;

    registerBlockType('wp-roadmap-pro/display-ideas', {
        title: __('Display Ideas', 'wp-roadmap-pro'),
        category: 'common',
        edit: function() {
            return createElement('p', {}, __('Display Ideas | This block will display your published ideas', 'wp-roadmap-pro'));
        },
        save: function() {
            return null; // Render via PHP
        },
    });

    // registerBlockType('wp-roadmap-pro/new-idea-form', {
    //     title: __('New Idea Form', 'wp-roadmap-pro'),
    //     category: 'common',
    //     edit: function() {
    //         return createElement('p', {}, __('New Idea Form | This block displays the form users will use to submit new ideas', 'wp-roadmap-pro'));
    //     },
    //     save: function() {
    //         return null; // Render via PHP
    //     },
    // });

    registerBlockType('wp-roadmap-pro/single-idea', {
        title: __('Single Idea', 'wp-roadmap-pro'),
        category: 'common',
        edit: function() {
            return createElement('p', {}, __('Single Idea | This block displays a single Idea', 'wp-roadmap-pro'));
        },
        save: function() {
            return null; // Render via PHP
        },
    });

    
})();
