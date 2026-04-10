import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls
} from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    RangeControl
} from '@wordpress/components';

import metadata from './blocks/list.json';

registerBlockType(metadata.name, {
    edit({ attributes, setAttributes }) {
        const { view_mode, limit } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Program List Settings" initialOpen={true}>
                        <SelectControl
                            label="View Mode"
                            value={view_mode}
                            options={[
                                { label: 'List', value: 'list' },
                                { label: 'Grid', value: 'grid' }
                            ]}
                            onChange={(value) =>
                                setAttributes({ view_mode: value })
                            }
                        />

                        <RangeControl
                            label="Number of items"
                            value={limit}
                            min={1}
                            max={20}
                            onChange={(value) =>
                                setAttributes({ limit: value })
                            }
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...useBlockProps()}>
                    <strong>Program List</strong>
                    <p style={{ marginTop: '8px', opacity: 0.7 }}>
                        Mode: <em>{view_mode}</em><br />
                        Limit: <em>{limit}</em>
                    </p>
                </div>
            </>
        );
    },

    save() {
        return null; // Dynamic block
    }
});
