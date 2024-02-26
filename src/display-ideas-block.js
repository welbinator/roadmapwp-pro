const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, CheckboxControl } = wp.components;
const { useSelect } = wp.data;
const { __ } = wp.i18n;

registerBlockType('roadmapwp-pro/display-ideas', {
    title: __('Display Ideas', 'roadmapwp-pro'),
    category: 'roadmap',
    icon: 'lightbulb',
    attributes: {
        onlyLoggedInUsers: {
            type: 'boolean',
            default: false,
        },
    },
    edit: ({ attributes, setAttributes }) => {
        console.log('onlyLoggedInUsers attribute value:', attributes.onlyLoggedInUsers);

        return (
            <div {...useBlockProps()}>
                <InspectorControls>
                <PanelBody title="Access Control">
                        <CheckboxControl
                            label="Allow only logged in users to see this form?"
                            checked={attributes.onlyLoggedInUsers}
                            onChange={(isChecked) => {
                                // Correctly placed logging statement within the onChange handler
                                console.log('Checkbox onChange triggered. New Value:', isChecked);
                                setAttributes({ onlyLoggedInUsers: isChecked });
                            }}
                        />
                    </PanelBody>
                </InspectorControls>
                <p>{__('Display Ideas | This block will display your published ideas', 'roadmapwp-pro')}</p>
            </div>
        );
    },
    save: () => {
        // Content is rendered dynamically via PHP and attributes are saved to the database
        return null;
    },
});
