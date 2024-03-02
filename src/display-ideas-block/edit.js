import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                <PanelBody title={__("Access Control", "roadmapwp-pro")}>
                    <CheckboxControl
                        label={__("Allow only logged in users to see this block?", "roadmapwp-pro")}
                        checked={attributes.onlyLoggedInUsers}
                        onChange={(isChecked) => setAttributes({ onlyLoggedInUsers: isChecked })}
                    />
                </PanelBody>
            </InspectorControls>
            <p>{__("Display Ideas | This block will display your published ideas", "roadmapwp-pro")}</p>
        </div>
    );
}
