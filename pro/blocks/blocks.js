(function() {
    const { registerBlockType } = wp.blocks;
    const { createElement } = wp.element;
    const { InspectorControls, CheckboxControl } = wp.blockEditor;
    const { __ } = wp.i18n;

    registerBlockType('roadmapwp-pro/display-ideas', {
        title: __('Display Ideas', 'roadmapwp-pro'),
        category: 'common',
        edit: function() {
            return createElement('p', {}, __('Display Ideas | This block will display your published ideas', 'roadmapwp-pro'));
        },
        save: function() {
            return null; // Render via PHP
        },
    });

    // registerBlockType('roadmapwp-pro/new-idea-form', {
    //     title: __('New Idea Form', 'roadmapwp-pro'),
    //     category: 'common',
    //     edit: function() {
    //         return createElement('p', {}, __('New Idea Form | This block displays the form users will use to submit new ideas', 'roadmapwp-pro'));
    //     },
    //     save: function() {
    //         return null; // Render via PHP
    //     },
    // });

    registerBlockType('roadmapwp-pro/single-idea', {
        title: __('Single Idea', 'roadmapwp-pro'),
        category: 'common',
        edit: function() {
            return createElement('p', {}, __('Single Idea | This block displays a single Idea', 'roadmapwp-pro'));
        },
        save: function() {
            return null; // Render via PHP
        },
    });

    
})();
