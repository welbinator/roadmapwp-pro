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
        selectedTaxonomies: {
            type: 'object',
            default: {},
        },
    },

    edit: ({ attributes, setAttributes }) => {
        // Fetch statuses
        const statuses = useSelect(select => {
            return select('core').getEntityRecords('taxonomy', 'status', { per_page: -1 });
        }, []);

        // Update function for statuses
        const updateSelectedStatuses = (termId, isChecked) => {
            const newStatuses = { ...attributes.selectedStatuses, [termId]: isChecked };
            setAttributes({ selectedStatuses: newStatuses });
        };

        // Fetch other taxonomies associated with 'idea' post type, excluding 'status'
        const taxonomies = useSelect(select => {
            const allTaxonomies = select('core').getTaxonomies();
            return allTaxonomies ? allTaxonomies.filter(tax => tax.types.includes('idea') && tax.slug !== 'status') : [];
        }, []);

        // Update function for taxonomies
        const updateSelectedTaxonomies = (taxonomySlug, isChecked) => {
            const newTaxonomies = { ...attributes.selectedTaxonomies, [taxonomySlug]: isChecked };
            setAttributes({ selectedTaxonomies: newTaxonomies });
        };

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
                    <PanelBody title="Idea Taxonomies">
                        {taxonomies && taxonomies.map(taxonomy => (
                            <CheckboxControl
                                key={taxonomy.slug}
                                label={taxonomy.name}
                                checked={!!attributes.selectedTaxonomies[taxonomy.slug]}
                                onChange={(isChecked) => updateSelectedTaxonomies(taxonomy.slug, isChecked)}
                            />
                        ))}
                    </PanelBody>
                </InspectorControls>
                <p>New Idea Form will be displayed here.</p>
            </div>
        );
    },

    save: ({ attributes }) => {
        // Convert selected statuses to a comma-separated string
        const selectedStatuses = Object.keys(attributes.selectedStatuses)
            .filter(id => attributes.selectedStatuses[id])
            .join(',');

        // Convert selected taxonomies to a comma-separated string
        const selectedTaxonomies = Object.keys(attributes.selectedTaxonomies)
            .filter(slug => attributes.selectedTaxonomies[slug])
            .join(',');

        return (
            <div data-selected-statuses={selectedStatuses} data-selected-taxonomies={selectedTaxonomies}>
                <p>New Idea Form will be displayed here.</p>
            </div>
        );
    },
});
