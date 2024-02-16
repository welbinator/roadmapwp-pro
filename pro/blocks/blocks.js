(function() {
    const { registerBlockType } = wp.blocks;
    const { createElement } = wp.element;
    const { InspectorControls, CheckboxControl } = wp.blockEditor;
    const { __ } = wp.i18n;

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
