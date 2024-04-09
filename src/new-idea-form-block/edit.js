import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
    // Fetch statuses
	const statuses = useSelect(select => {
		
		return select('core').getEntityRecords('taxonomy', 'idea-status', { per_page: -1 });
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
		return allTaxonomies ? allTaxonomies.filter(tax => tax.types.includes('idea') && tax.slug !== 'idea-status') : [];
	}, []);
	console.log('taxonomies are:', taxonomies);

	// Update function for taxonomies
	const updateSelectedTaxonomies = (taxonomySlug, isChecked) => {
		const newTaxonomies = { ...attributes.selectedTaxonomies, [taxonomySlug]: isChecked };
		setAttributes({ selectedTaxonomies: newTaxonomies });
	};

	// hook to fetch courses.
    const courses = useSelect((select) => {
        return select('core').getEntityRecords('postType', 'sfwd-courses', { per_page: -1 });
    }, []);

    // Function to handle course checkbox change
    const onCourseCheckboxChange = (courseId, isChecked) => {
        // Ensure selectedCourses is an array, fallback to empty array if not
        const currentSelectedCourses = Array.isArray(attributes.selectedCourses) ? attributes.selectedCourses : [];
        
        let updatedSelectedCourses;
        if (isChecked) {
            // Add the courseId to the array if it's checked and not already present
            updatedSelectedCourses = [...currentSelectedCourses, courseId];
        } else {
            // Remove the courseId from the array if it's unchecked
            updatedSelectedCourses = currentSelectedCourses.filter(id => id !== courseId);
        }
    
        // Update the block's attributes with the new array of selected course IDs
        setAttributes({ selectedCourses: updatedSelectedCourses });
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
				{window.learndashIsActive && window.learndashIsActive.active && (
                    <PanelBody title={__("Allow only students enrolled in the following courses to see this block:", "roadmapwp-pro")}>
                        {courses && courses.map(course => (
                            <CheckboxControl
                                key={course.id}
                                label={course.title.rendered}
                                checked={attributes.selectedCourses ? attributes.selectedCourses.includes(course.id) : false}
                                onChange={(isChecked) => onCourseCheckboxChange(course.id, isChecked)}
                            />
                        ))}
                    </PanelBody>
                )}
			</InspectorControls>
			<p>New Idea Form will be displayed here.</p>
		</div>
	);
}
