const { registerBlockType } = wp.blocks;
const { useSelect } = wp.data;
const { CheckboxControl, PanelBody } = wp.components;
const { InspectorControls } = wp.blockEditor;

registerBlockType('wp-roadmap-pro/roadmap-tabs-block', {
    title: 'Roadmap Tabs Block',
    category: 'common',
    attributes: {
        selectedStatuses: {
            type: 'object',
            default: {},
        },
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;
        const statuses = useSelect(select => {
            return select('core').getEntityRecords('taxonomy', 'status', { per_page: -1 });
        }, []);

        const updateSelectedStatuses = (termSlug, isChecked) => {
            const newStatuses = { ...attributes.selectedStatuses, [termSlug]: isChecked };
            setAttributes({ selectedStatuses: newStatuses });
        };

        return (
            <div>
                <InspectorControls>
                    <PanelBody title="Select Statuses">
                        {statuses && statuses.map(term => (
                            <CheckboxControl
                                label={term.name}
                                checked={!!attributes.selectedStatuses[term.slug]}
                                onChange={(isChecked) => updateSelectedStatuses(term.slug, isChecked)}
                            />
                        ))}
                    </PanelBody>
                </InspectorControls>
                <p>Roadmap Tabs Block</p>
            </div>
        );
    },
    save: function() {
        return null; // Render in PHP
    }
});
