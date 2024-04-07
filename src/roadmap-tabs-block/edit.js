import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';


export default function Edit({ attributes, setAttributes }) {
    // Fetch statuses and taxonomies using hooks at the top level
    const statuses = useSelect(select => select('core').getEntityRecords('taxonomy', 'idea-status', { per_page: -1 }), []);
    const ideaTaxonomies = useSelect(select => {
        const allTaxonomies = select('core').getTaxonomies();
        return allTaxonomies ? allTaxonomies.filter(tax => tax.types.includes('idea') && tax.slug !== 'idea-status') : [];
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

    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                <PanelBody title="Select Statuses">
                    {statuses ? (
                        statuses.map(term => (
                            <CheckboxControl
                                key={term.id}
                                label={term.name}
                                checked={!!attributes.selectedStatuses[term.slug]}
                                onChange={(isChecked) => updateSelectedStatuses(term.slug, isChecked)}
                            />
                        ))
                    ) : (
                        <p>Loading statuses...</p>
                    )}
                </PanelBody>
                <PanelBody title="Select Taxonomies">
                    {ideaTaxonomies ? (
                        ideaTaxonomies.map(taxonomy => (
                            <CheckboxControl
                                key={taxonomy.slug}
                                label={taxonomy.name}
                                checked={!!attributes.selectedTaxonomies[taxonomy.slug]}
                                onChange={(isChecked) => updateSelectedTaxonomies(taxonomy.slug, isChecked)}
                            />
                        ))
                    ) : (
                        <p>Loading taxonomies...</p>
                    )}
                </PanelBody>
                <PanelBody title="Default Status">
					<SelectControl
						label="Select a Default Status"
						value={attributes.defaultStatus}
						options={[
							{ label: 'Select Status', value: '' },
							...(statuses ? statuses.map(status => ({
								label: status.name,
								value: status.slug,
							})) : []),
						]}
						onChange={(value) => setAttributes({ defaultStatus: value })}
					/>
				</PanelBody>
				<PanelBody title="Access Control">
					<CheckboxControl
						label="Allow only logged in users to see this block?"
						checked={attributes.onlyLoggedInUsers}
						onChange={(isChecked) => setAttributes({ onlyLoggedInUsers: isChecked })}
					/>
				</PanelBody>
            </InspectorControls>
            <p>Roadmap Tabs Block</p>
        </div>
    );
}