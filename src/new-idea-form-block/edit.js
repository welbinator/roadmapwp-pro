import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
    // Fetch statuses
	const statuses = useSelect(select => {
		
		return select('core').getEntityRecords('taxonomy', 'status', { per_page: -1 });
	}, []);
	console.log('statuses are:', statuses);

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
	console.log('taxonomies are:', taxonomies);

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
				<PanelBody title="Access Control">
					<CheckboxControl
						label="Allow only logged in users to see this form?"
						checked={attributes.onlyLoggedInUsers}
						onChange={(isChecked) => setAttributes({ onlyLoggedInUsers: isChecked })}
					/>
				</PanelBody>
			</InspectorControls>
			<p>New Idea Form will be displayed here.</p>
		</div>
	);
}
