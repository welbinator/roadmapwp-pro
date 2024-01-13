const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, CheckboxControl } = wp.components;
const { useSelect } = wp.data;

registerBlockType('wp-roadmap-pro/new-idea-form', {
    title: 'New Idea Form',
    category: 'common',
    attributes: {
        selectedStatuses: {
            type: 'object',
            default: {},
        },
    },

    edit: ({ attributes, setAttributes }) => {
        // Fetching the statuses using useSelect
        const statuses = useSelect(select => {
            return select('core').getEntityRecords('taxonomy', 'status', { per_page: -1 });
        }, []);

        // Function to update selected statuses
        const updateSelectedStatuses = (termId, isChecked) => {
            const newStatuses = { ...attributes.selectedStatuses, [termId]: isChecked };
            setAttributes({ selectedStatuses: newStatuses });
            console.log('Updated selectedStatuses:', newStatuses); // Debugging line
        };

        // Render the block editor interface
        return (
            <div {...useBlockProps()}>
                <InspectorControls>
                    <PanelBody title="Select Statuses for New Idea">
                        {statuses && statuses.map(term => (
                            <CheckboxControl
                                key={term.id}
                                label={term.name}
                                checked={!!attributes.selectedStatuses[term.id]}
                                onChange={(isChecked) => updateSelectedStatuses(term.id, isChecked)}
                            />
                        ))}
                    </PanelBody>
                </InspectorControls>
                <p>New Idea Form will be displayed here.</p>
            </div>
        );
    },

    save: ({ attributes }) => {
        const selectedStatuses = Object.keys(attributes.selectedStatuses)
            .filter(id => attributes.selectedStatuses[id])
            .join(',');
    
        console.log('Saving with selectedStatuses:', selectedStatuses);  // Debugging line
    
        return (
            <div data-selected-statuses={selectedStatuses}>
                <p>New Idea Form will be displayed here.</p>
            </div>
        );
    },
    
    
    
    
});
