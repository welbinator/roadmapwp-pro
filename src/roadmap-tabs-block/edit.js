import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';


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
            <p>Roadmap Tabs Block</p>
        </div>
    );
}