(function () {
    if (window.hblBusinessDetailsBlockLoaded) return;
    window.hblBusinessDetailsBlockLoaded = true;

    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;
    const { SelectControl } = wp.components;
    const { useEffect, useState } = wp.element;
    const { createElement } = wp.element;

    const Edit = (props) => {
        const [businesses, setBusinesses] = useState([]);
        const { attributes, setAttributes } = props;

        useEffect(() => {
            wp.apiFetch({ path: '/happy-business-listing/v1/businesses' }).then(
                (fetchedBusinesses) => {
                    setBusinesses([{ value: 0, label: 'Select a business' }, ...fetchedBusinesses]);
                }
            );
        }, []);

        return createElement(
            'div',
            null,
            createElement(SelectControl, {
                label: __('Select Business', 'happy-business-listing'),
                value: attributes.businessId,
                options: businesses,
                onChange: (businessId) => setAttributes({ businessId: parseInt(businessId) })
            })
        );
    };

    registerBlockType('happy-business-listing/business-details', {
        title: __('Business Details', 'happy-business-listing'),
        icon: 'building',
        category: 'common',
        attributes: {
            businessId: {
                type: 'number',
                default: 0,
            },
        },
        edit: Edit,
        save: () => null, // We're using server-side rendering
    });

    console.log('Business Details block registered');
})();