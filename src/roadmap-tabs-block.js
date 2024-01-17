const { registerBlockType } = wp.blocks;
const { useSelect } = wp.data;
const { CheckboxControl, PanelBody, SelectControl } = wp.components;
const { InspectorControls } = wp.blockEditor;

registerBlockType('wp-roadmap-pro/roadmap-tabs-block', {
    title: 'Roadmap Tabs Block',
    category: 'common',
    attributes: {
        selectedStatuses: {
            type: 'object',
            default: {},
        },
        selectedTaxonomies: {
            type: 'object',
            default: {},
        },
        defaultStatus: {
            type: 'string',
            default: '',
        },
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;
    
        // Fetch statuses and taxonomies using hooks at the top level
        const statuses = useSelect(select => select('core').getEntityRecords('taxonomy', 'status', { per_page: -1 }), []);
        const ideaTaxonomies = useSelect(select => {
            const allTaxonomies = select('core').getTaxonomies();
            return allTaxonomies ? allTaxonomies.filter(tax => tax.types.includes('idea') && tax.slug !== 'status') : [];
        }, []);
    
        // Update functions for selected statuses and taxonomies
        const updateSelectedStatuses = (termSlug, isChecked) => {
            const newStatuses = { ...attributes.selectedStatuses, [termSlug]: isChecked };
            setAttributes({ selectedStatuses: newStatuses });
        };
        const updateSelectedTaxonomies = (taxonomySlug, isChecked) => {
            const newTaxonomies = { ...attributes.selectedTaxonomies, [taxonomySlug]: isChecked };
            setAttributes({ selectedTaxonomies: newTaxonomies });
        };
    
        // Conditional rendering after hooks
        if (!statuses) {
            return <p>Loading statuses...</p>;
        }
    
        return (
            <div>
                <InspectorControls>
                    <PanelBody title="Select Statuses">
                        {statuses.map(term => (
                            <CheckboxControl
                                key={term.id}
                                label={term.name}
                                checked={!!attributes.selectedStatuses[term.slug]}
                                onChange={(isChecked) => updateSelectedStatuses(term.slug, isChecked)}
                            />
                        ))}
                    </PanelBody>
                    <PanelBody title="Select Taxonomies">
                        {ideaTaxonomies && ideaTaxonomies.map(taxonomy => (
                            <CheckboxControl
                                key={taxonomy.slug}
                                label={taxonomy.name}
                                checked={!!attributes.selectedTaxonomies[taxonomy.slug]}
                                onChange={(isChecked) => updateSelectedTaxonomies(taxonomy.slug, isChecked)}
                            />
                        ))}
                    </PanelBody>
                    <PanelBody title="Default Status">
                        <SelectControl
                            label="Select a Default Status"
                            value={attributes.defaultStatus}
                            options={[
                                { label: 'Select Status', value: '' },
                                ...statuses.map(status => ({
                                    label: status.name,
                                    value: status.slug,
                                })),
                            ]}
                            onChange={(value) => setAttributes({ defaultStatus: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                <p>Roadmap Tabs Block</p>
            </div>
        );
    },
    save: function() {
        // Content is rendered in PHP, return null for the save function
        return null;
    }
});
