/**
 * Gutenberg Blocks for Happy Business Listing
 */
(function(blocks, editor, components, i18n, element) {
    var el = element.createElement;
    var __ = i18n.__;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;
    var RangeControl = components.RangeControl;
    var SelectControl = components.SelectControl;
    var ToggleControl = components.ToggleControl;
    var Placeholder = components.Placeholder;
    var ServerSideRender = components.ServerSideRender;
    
    // Register Business Listings Block
    registerBlockType('hbl/business-listings', {
        title: __('Business Listings', 'happy-business-listing'),
        icon: 'store',
        category: 'happy-business-listing',
        keywords: [
            __('business', 'happy-business-listing'),
            __('listing', 'happy-business-listing'),
            __('directory', 'happy-business-listing'),
        ],
        attributes: {
            numberOfItems: {
                type: 'number',
                default: 3,
            },
            orderBy: {
                type: 'string',
                default: 'date',
            },
            order: {
                type: 'string',
                default: 'desc',
            },
            displayFeaturedImage: {
                type: 'boolean',
                default: true,
            },
            displayExcerpt: {
                type: 'boolean',
                default: true,
            },
            displayCompanyType: {
                type: 'boolean',
                default: true,
            },
            displayLocation: {
                type: 'boolean',
                default: true,
            },
            columns: {
                type: 'number',
                default: 3,
            },
        },
        
        edit: function(props) {
            var attributes = props.attributes;
            
            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Listing Settings', 'happy-business-listing'), initialOpen: true },
                        el(RangeControl, {
                            label: __('Number of Items', 'happy-business-listing'),
                            value: attributes.numberOfItems,
                            onChange: function(value) {
                                props.setAttributes({ numberOfItems: value });
                            },
                            min: 1,
                            max: 20,
                        }),
                        
                        el(SelectControl, {
                            label: __('Order By', 'happy-business-listing'),
                            value: attributes.orderBy,
                            options: [
                                { label: __('Date', 'happy-business-listing'), value: 'date' },
                                { label: __('Title', 'happy-business-listing'), value: 'title' },
                                { label: __('Random', 'happy-business-listing'), value: 'rand' },
                            ],
                            onChange: function(value) {
                                props.setAttributes({ orderBy: value });
                            },
                        }),
                        
                        el(SelectControl, {
                            label: __('Order', 'happy-business-listing'),
                            value: attributes.order,
                            options: [
                                { label: __('Descending', 'happy-business-listing'), value: 'desc' },
                                { label: __('Ascending', 'happy-business-listing'), value: 'asc' },
                            ],
                            onChange: function(value) {
                                props.setAttributes({ order: value });
                            },
                        }),
                        
                        el(RangeControl, {
                            label: __('Columns', 'happy-business-listing'),
                            value: attributes.columns,
                            onChange: function(value) {
                                props.setAttributes({ columns: value });
                            },
                            min: 1,
                            max: 4,
                        }),
                    ),
                    
                    el(PanelBody, { title: __('Display Options', 'happy-business-listing'), initialOpen: false },
                        el(ToggleControl, {
                            label: __('Display Featured Image', 'happy-business-listing'),
                            checked: attributes.displayFeaturedImage,
                            onChange: function() {
                                props.setAttributes({ displayFeaturedImage: !attributes.displayFeaturedImage });
                            },
                        }),
                        
                        el(ToggleControl, {
                            label: __('Display Excerpt', 'happy-business-listing'),
                            checked: attributes.displayExcerpt,
                            onChange: function() {
                                props.setAttributes({ displayExcerpt: !attributes.displayExcerpt });
                            },
                        }),
                        
                        el(ToggleControl, {
                            label: __('Display Company Type', 'happy-business-listing'),
                            checked: attributes.displayCompanyType,
                            onChange: function() {
                                props.setAttributes({ displayCompanyType: !attributes.displayCompanyType });
                            },
                        }),
                        
                        el(ToggleControl, {
                            label: __('Display Location', 'happy-business-listing'),
                            checked: attributes.displayLocation,
                            onChange: function() {
                                props.setAttributes({ displayLocation: !attributes.displayLocation });
                            },
                        }),
                    )
                ),
                
                el(ServerSideRender, {
                    block: 'hbl/business-listings',
                    attributes: attributes,
                }),
            ];
        },
        
        save: function() {
            return null; // Server-side rendered
        },
    });
    
    // Register Business Search Block
    registerBlockType('hbl/business-search', {
        title: __('Business Search', 'happy-business-listing'),
        icon: 'search',
        category: 'happy-business-listing',
        keywords: [
            __('business', 'happy-business-listing'),
            __('search', 'happy-business-listing'),
            __('filter', 'happy-business-listing'),
        ],
        attributes: {
            showFilters: {
                type: 'boolean',
                default: true,
            },
        },
        
        edit: function(props) {
            var attributes = props.attributes;
            
            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Search Settings', 'happy-business-listing'), initialOpen: true },
                        el(ToggleControl, {
                            label: __('Show Filters', 'happy-business-listing'),
                            checked: attributes.showFilters,
                            onChange: function() {
                                props.setAttributes({ showFilters: !attributes.showFilters });
                            },
                        }),
                    )
                ),
                
                el(Placeholder, {
                    icon: 'search',
                    label: __('Business Search', 'happy-business-listing'),
                    className: 'hbl-block-placeholder',
                },
                    el('p', { className: 'hbl-block-placeholder-help' },
                        __('This block displays a search form for business listings.', 'happy-business-listing')
                    ),
                    el('p', {},
                        attributes.showFilters
                            ? __('Filters are enabled.', 'happy-business-listing')
                            : __('Filters are disabled.', 'happy-business-listing')
                    )
                ),
            ];
        },
        
        save: function() {
            return null; // Server-side rendered
        },
    });
    
    // Register Business Details Block
    registerBlockType('hbl/business-details', {
        title: __('Business Details', 'happy-business-listing'),
        icon: 'id',
        category: 'happy-business-listing',
        keywords: [
            __('business', 'happy-business-listing'),
            __('details', 'happy-business-listing'),
            __('profile', 'happy-business-listing'),
        ],
        attributes: {
            businessId: {
                type: 'number',
                default: 0,
            },
            showTitle: {
                type: 'boolean',
                default: true,
            },
            showImage: {
                type: 'boolean',
                default: true,
            },
            showDetails: {
                type: 'boolean',
                default: true,
            },
            showContent: {
                type: 'boolean',
                default: true,
            },
        },
        
        edit: function(props) {
            var attributes = props.attributes;
            
            // Get business listings for select control
            var businessOptions = [
                { label: __('Current Business Listing', 'happy-business-listing'), value: 0 }
            ];
            
            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Business Selection', 'happy-business-listing'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Select Business', 'happy-business-listing'),
                            value: attributes.businessId,
                            options: businessOptions,
                            onChange: function(value) {
                                props.setAttributes({ businessId: parseInt(value) });
                            },
                        }),
                    ),
                    
                    el(PanelBody, { title: __('Display Options', 'happy-business-listing'), initialOpen: true },
                        el(ToggleControl, {
                            label: __('Show Title', 'happy-business-listing'),
                            checked: attributes.showTitle,
                            onChange: function() {
                                props.setAttributes({ showTitle: !attributes.showTitle });
                            },
                        }),
                        
                        el(ToggleControl, {
                            label: __('Show Image', 'happy-business-listing'),
                            checked: attributes.showImage,
                            onChange: function() {
                                props.setAttributes({ showImage: !attributes.showImage });
                            },
                        }),
                        
                        el(ToggleControl, {
                            label: __('Show Details', 'happy-business-listing'),
                            checked: attributes.showDetails,
                            onChange: function() {
                                props.setAttributes({ showDetails: !attributes.showDetails });
                            },
                        }),
                        
                        el(ToggleControl, {
                            label: __('Show Content', 'happy-business-listing'),
                            checked: attributes.showContent,
                            onChange: function() {
                                props.setAttributes({ showContent: !attributes.showContent });
                            },
                        }),
                    )
                ),
                
                el(Placeholder, {
                    icon: 'id',
                    label: __('Business Details', 'happy-business-listing'),
                    className: 'hbl-block-placeholder',
                },
                    el('p', { className: 'hbl-block-placeholder-help' },
                        __('This block displays details of a business listing.', 'happy-business-listing')
                    ),
                    el('p', {},
                        attributes.businessId === 0
                            ? __('Using current business listing.', 'happy-business-listing')
                            : __('Using selected business listing.', 'happy-business-listing')
                    )
                ),
            ];
        },
        
        save: function() {
            return null; // Server-side rendered
        },
    });
})(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n,
    window.wp.element
);