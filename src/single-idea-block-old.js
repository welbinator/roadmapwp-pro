const { registerBlockType } = wp.blocks;
const { TextControl, PanelBody } = wp.components;
const { InspectorControls, useBlockProps } = wp.blockEditor;

registerBlockType('roadmapwp-pro/single-idea', {
    title: 'Single Idea',
    category: 'roadmap',
    icon: 'lightbulb',
    attributes: {
        ideaId: {
            type: 'number',
            default: 0,
        },
        onlyLoggedInUsers: {
            type: 'boolean',
            default: false,
        },
    },
    
    edit: function(props) {
        const { attributes, setAttributes } = props;
        const blockProps = useBlockProps();

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title="Single Idea Settings">
                        <TextControl
                            label="Idea ID"
                            value={attributes.ideaId}
                            onChange={(value) => setAttributes({ ideaId: parseInt(value, 10) || 0 })}
                        />
                        <CheckboxControl
                            label="Allow only logged in users to see this idea?"
                            checked={attributes.onlyLoggedInUsers}
                            onChange={(isChecked) => setAttributes({ onlyLoggedInUsers: isChecked })}
                        />
                    </PanelBody>
                </InspectorControls>
                <p>Single Idea Block Placeholder</p>
            </div>
        );
    },
    
    save: function() {
        return null; // Content will be rendered through PHP
    }
});
