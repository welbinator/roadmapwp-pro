import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

export default function Edit({ attributes, setAttributes }) {
    
    // Function to get query parameter by name
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    useEffect(() => {
        // Only set ideaId from URL if it's not already set
        if (!attributes.ideaId || attributes.ideaId === 0) {
            const ideaIdFromURL = parseInt(getQueryParam('idea_id'), 10);
            if (ideaIdFromURL && !isNaN(ideaIdFromURL)) {
                setAttributes({ ideaId: ideaIdFromURL });
            }
        }
    }, []);

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
            <p>Single Idea Block Placeholder</p>
        </div>
    );
}
