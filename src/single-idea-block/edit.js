import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { TextControl, PanelBody, CheckboxControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

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

    // block preview
  
    return (
        <div {...useBlockProps()}>
            
            <p>Single Idea Block Placeholder</p>
        </div>
    );
}
