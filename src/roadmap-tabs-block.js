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
        selectedTaxonomies: {
            type: 'object',
            default: {},
        },
    },
    edit: function(props) {
        const { attributes, setAttributes } = props;

        // Fetch statuses
        const statuses = useSelect(select => select('core').getEntityRecords('taxonomy', 'status', { per_page: -1 }), []);

        // Fetch taxonomies associated with 'idea' post type and exclude 'status'
        const ideaTaxonomies = useSelect(select => {
            const allTaxonomies = select('core').getTaxonomies();
            return allTaxonomies ? allTaxonomies.filter(tax => tax.types.includes('idea') && tax.slug !== 'status') : [];
        }, []);

        const updateSelectedStatuses = (termSlug, isChecked) => {
            const newStatuses = { ...attributes.selectedStatuses, [termSlug]: isChecked };
            setAttributes({ selectedStatuses: newStatuses });
        };

        const updateSelectedTaxonomies = (taxonomySlug, isChecked) => {
            const newTaxonomies = { ...attributes.selectedTaxonomies, [taxonomySlug]: isChecked };
            setAttributes({ selectedTaxonomies: newTaxonomies });
        };

        return (
            <div>
                <InspectorControls>
                    <PanelBody title="Select Statuses">
                        {statuses && statuses.map(term => (
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
                </InspectorControls>
                <p>Roadmap Tabs Block</p>
            </div>
        );
    },
    save: function() {
        return null; // Render in PHP
    }
});
