import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl, RadioControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	// const { attributes, setAttributes } = props;
            const statuses = useSelect(select => {
                return select('core').getEntityRecords('taxonomy', 'status', { per_page: -1 });
            }, []);
        
            const updateSelectedStatuses = (termSlug, isChecked) => {
                const newStatuses = { ...attributes.selectedStatuses, [termSlug]: isChecked };
                setAttributes({ selectedStatuses: newStatuses });
            };
        
            return (
                <div {...useBlockProps()}>
                    <InspectorControls>
                        <PanelBody title="Select Statuses">
                            {statuses && statuses.map(term => (
                                <CheckboxControl
                                    label={term.name}
                                    checked={!!attributes.selectedStatuses[term.slug]}
                                    onChange={(isChecked) => updateSelectedStatuses(term.slug, isChecked)}
                                />
                            ))}
                            <PanelBody title="Status Filter">
                                <RadioControl
                                    label="Idea Status"
                                    selected={attributes.statusFilter}
                                    options={[
                                        { label: 'Show only published ideas', value: 'published' },
                                        { label: 'Include ideas pending review', value: 'include_pending' },
                                    ]}
                                    onChange={(value) => setAttributes({ statusFilter: value })}
                                />
                            </PanelBody>
                        </PanelBody>
                        <PanelBody title="Access Control">
                        <CheckboxControl
                            label="Allow only logged in users to see this block?"
                            checked={attributes.onlyLoggedInUsers}
                            onChange={(isChecked) => setAttributes({ onlyLoggedInUsers: isChecked })}
                        />
                    </PanelBody>
                    </InspectorControls>
                    <p>Roadmap Block</p>
                </div>
            );
}
