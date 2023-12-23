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

registerBlockType('wp-roadmap-pro/new-idea-form', {
    title: __('New Idea Form', 'wp-roadmap-pro'),
    category: 'common',
    edit: function() {
        return createElement('p', {}, __('New Idea Form | This block displays the form users will use to submit new ideas', 'wp-roadmap-pro'));
    },
    save: function() {
        return null; // Render via PHP
    },
});

registerBlockType('wp-roadmap-pro/roadmap', {
    title: __('Roadmap', 'wp-roadmap-pro'),
    category: 'common',
    attributes: {
        showNewIdea: { type: 'boolean', default: true },
        showUpNext: { type: 'boolean', default: true },
        showMaybe: { type: 'boolean', default: true },
        showOnRoadmap: { type: 'boolean', default: true },
        showClosed: { type: 'boolean', default: true },
    },
    edit: function(props) {
        const { attributes: { showNewIdea, showUpNext, showMaybe, showOnRoadmap, showClosed }, setAttributes } = props;

        return wp.element.createElement(
            'div', 
            {},
            wp.element.createElement(wp.blockEditor.InspectorControls, null,
                wp.element.createElement(wp.components.PanelBody, {},
                    wp.element.createElement(wp.components.CheckboxControl, {
                        label: __('Show New Idea', 'wp-roadmap-pro'),
                        checked: showNewIdea,
                        onChange: (newVal) => setAttributes({ showNewIdea: newVal }),
                    }),
                    wp.element.createElement(wp.components.CheckboxControl, {
                        label: __('Show Up Next', 'wp-roadmap-pro'),
                        checked: showUpNext,
                        onChange: (newVal) => setAttributes({ showUpNext: newVal }),
                    }),
                    wp.element.createElement(wp.components.CheckboxControl, {
                        label: __('Show Maybe', 'wp-roadmap-pro'),
                        checked: showMaybe,
                        onChange: (newVal) => setAttributes({ showMaybe: newVal }),
                    }),
                    wp.element.createElement(wp.components.CheckboxControl, {
                        label: __('Show On Roadmap', 'wp-roadmap-pro'),
                        checked: showOnRoadmap,
                        onChange: (newVal) => setAttributes({ showOnRoadmap: newVal }),
                    }),
                    wp.element.createElement(wp.components.CheckboxControl, {
                        label: __('Show Closed', 'wp-roadmap-pro'),
                        checked: showClosed,
                        onChange: (newVal) => setAttributes({ showClosed: newVal }),
                    }),
                ),
            ),
            'Roadmap Block Content',
        );
    },
    
    
    
    save: function() {
        return null; // Render via PHP
    },
});
