import { useSelect } from '@wordpress/data';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();

    const businesses = useSelect((select) => {
        return select('core').getEntityRecords('postType', 'business_listing', { per_page: -1 });
    }, []);

    const businessOptions = businesses ? [
        { value: 0, label: __('Select a business', 'happy-business-listing') },
        ...businesses.map((business) => ({ value: business.id, label: business.title.rendered }))
    ] : [];

    const businessDetails = useSelect((select) => {
        return attributes.businessId ? select('core').getEntityRecord('postType', 'business_listing', attributes.businessId) : null;
    }, [attributes.businessId]);

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Business Selection', 'happy-business-listing')}>
                    <SelectControl
                        label={__('Select Business', 'happy-business-listing')}
                        value={attributes.businessId}
                        options={businessOptions}
                        onChange={(value) => setAttributes({ businessId: parseInt(value) })}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                {businessDetails ? (
                    <div className="business-details">
                        <h2>{businessDetails.title.rendered}</h2>
                        <p><strong>{__('Company Type:', 'happy-business-listing')}</strong> {businessDetails.meta.company_type}</p>
                        <p><strong>{__('Location:', 'happy-business-listing')}</strong> {businessDetails.meta.location}</p>
                        <p><strong>{__('Website:', 'happy-business-listing')}</strong> <a href={businessDetails.meta.website} target="_blank" rel="noopener noreferrer">{businessDetails.meta.website}</a></p>
                        <p><strong>{__('WhatsApp:', 'happy-business-listing')}</strong> {businessDetails.meta.whatsapp_number}</p>
                    </div>
                ) : (
                    <p>{__('Please select a business from the block settings.', 'happy-business-listing')}</p>
                )}
            </div>
        </>
    );
}