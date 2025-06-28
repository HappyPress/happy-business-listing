/**
 * Advanced Filter Block
 */
(function(blocks, element, components, blockEditor) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var ToggleControl = components.ToggleControl;
    var RangeControl = components.RangeControl;
    var ServerSideRender = components.ServerSideRender;
    
    // Register the block
    registerBlockType('happy-business-listing/advanced-filter', {
        title: 'Business Advanced Filter',
        icon: 'filter',
        category: 'widgets',
        keywords: ['business', 'filter', 'search', 'listing'],
        
        attributes: {
            title: {
                type: 'string',
                default: 'Find Businesses'
            },
            layout: {
                type: 'string',
                default: 'horizontal'
            },
            showKeywordSearch: {
                type: 'boolean',
                default: true
            },
            showLocationFilter: {
                type: 'boolean',
                default: true
            },
            showCategoryFilter: {
                type: 'boolean',
                default: true
            },
            showCompanyTypeFilter: {
                type: 'boolean',
                default: true
            },
            showRatingFilter: {
                type: 'boolean',
                default: false
            },
            showPriceRangeFilter: {
                type: 'boolean',
                default: false
            },
            showVerifiedFilter: {
                type: 'boolean',
                default: false
            },
            showSorting: {
                type: 'boolean',
                default: true
            },
            resultsPerPage: {
                type: 'number',
                default: 10
            },
            // New attributes
            showDateRangeFilter: {
                type: 'boolean',
                default: false
            },
            showServiceFilter: {
                type: 'boolean',
                default: false
            },
            showDistanceFilter: {
                type: 'boolean',
                default: false
            },
            showTagsFilter: {
                type: 'boolean',
                default: false
            },
            showOpenNowFilter: {
                type: 'boolean',
                default: false
            },
            maxDistanceOptions: {
                type: 'string',
                default: '5,10,25,50,100'
            },
            defaultDistanceUnit: {
                type: 'string',
                default: 'km'
            },
            enableAutoSubmit: {
                type: 'boolean',
                default: true
            },
            enableSavedFilters: {
                type: 'boolean',
                default: false
            },
            filterStyle: {
                type: 'string',
                default: 'standard'
            },
            showFilterToggle: {
                type: 'boolean',
                default: false
            }
        },
        
        // Edit function
        edit: function(props) {
            var attributes = props.attributes;
            
            // Inspector controls for block settings
            var inspectorControls = el(
                InspectorControls,
                {},
                el(
                    PanelBody,
                    {
                        title: 'Filter Settings',
                        initialOpen: true
                    },
                    el(
                        TextControl,
                        {
                            label: 'Title',
                            value: attributes.title,
                            onChange: function(value) {
                                props.setAttributes({ title: value });
                            }
                        }
                    ),
                    el(
                        SelectControl,
                        {
                            label: 'Layout',
                            value: attributes.layout,
                            options: [
                                { label: 'Horizontal', value: 'horizontal' },
                                { label: 'Vertical', value: 'vertical' },
                                { label: 'Grid', value: 'grid' },
                                { label: 'Compact', value: 'compact' }
                            ],
                            onChange: function(value) {
                                props.setAttributes({ layout: value });
                            }
                        }
                    ),
                    el(
                        SelectControl,
                        {
                            label: 'Filter Style',
                            value: attributes.filterStyle,
                            options: [
                                { label: 'Standard', value: 'standard' },
                                { label: 'Minimal', value: 'minimal' },
                                { label: 'Modern', value: 'modern' },
                                { label: 'Boxed', value: 'boxed' }
                            ],
                            onChange: function(value) {
                                props.setAttributes({ filterStyle: value });
                            }
                        }
                    ),
                    el(
                        RangeControl,
                        {
                            label: 'Results Per Page',
                            value: attributes.resultsPerPage,
                            min: 1,
                            max: 50,
                            onChange: function(value) {
                                props.setAttributes({ resultsPerPage: value });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Enable Auto-Submit',
                            checked: attributes.enableAutoSubmit,
                            onChange: function() {
                                props.setAttributes({ enableAutoSubmit: !attributes.enableAutoSubmit });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Filter Toggle (Mobile)',
                            checked: attributes.showFilterToggle,
                            onChange: function() {
                                props.setAttributes({ showFilterToggle: !attributes.showFilterToggle });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Enable Saved Filters',
                            checked: attributes.enableSavedFilters,
                            onChange: function() {
                                props.setAttributes({ enableSavedFilters: !attributes.enableSavedFilters });
                            }
                        }
                    )
                ),
                el(
                    PanelBody,
                    {
                        title: 'Basic Filter Options',
                        initialOpen: true
                    },
                    el(
                        ToggleControl,
                        {
                            label: 'Show Keyword Search',
                            checked: attributes.showKeywordSearch,
                            onChange: function() {
                                props.setAttributes({ showKeywordSearch: !attributes.showKeywordSearch });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Location Filter',
                            checked: attributes.showLocationFilter,
                            onChange: function() {
                                props.setAttributes({ showLocationFilter: !attributes.showLocationFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Category Filter',
                            checked: attributes.showCategoryFilter,
                            onChange: function() {
                                props.setAttributes({ showCategoryFilter: !attributes.showCategoryFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Company Type Filter',
                            checked: attributes.showCompanyTypeFilter,
                            onChange: function() {
                                props.setAttributes({ showCompanyTypeFilter: !attributes.showCompanyTypeFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Tags Filter',
                            checked: attributes.showTagsFilter,
                            onChange: function() {
                                props.setAttributes({ showTagsFilter: !attributes.showTagsFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Rating Filter',
                            checked: attributes.showRatingFilter,
                            onChange: function() {
                                props.setAttributes({ showRatingFilter: !attributes.showRatingFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Price Range Filter',
                            checked: attributes.showPriceRangeFilter,
                            onChange: function() {
                                props.setAttributes({ showPriceRangeFilter: !attributes.showPriceRangeFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Verified Filter',
                            checked: attributes.showVerifiedFilter,
                            onChange: function() {
                                props.setAttributes({ showVerifiedFilter: !attributes.showVerifiedFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Sorting Options',
                            checked: attributes.showSorting,
                            onChange: function() {
                                props.setAttributes({ showSorting: !attributes.showSorting });
                            }
                        }
                    )
                ),
                el(
                    PanelBody,
                    {
                        title: 'Advanced Filter Options',
                        initialOpen: false
                    },
                    el(
                        ToggleControl,
                        {
                            label: 'Show Date Range Filter',
                            checked: attributes.showDateRangeFilter,
                            onChange: function() {
                                props.setAttributes({ showDateRangeFilter: !attributes.showDateRangeFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Service Filter',
                            checked: attributes.showServiceFilter,
                            onChange: function() {
                                props.setAttributes({ showServiceFilter: !attributes.showServiceFilter });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show Distance-Based Filter',
                            checked: attributes.showDistanceFilter,
                            onChange: function() {
                                props.setAttributes({ showDistanceFilter: !attributes.showDistanceFilter });
                            }
                        }
                    ),
                    attributes.showDistanceFilter && el(
                        TextControl,
                        {
                            label: 'Distance Options (comma-separated)',
                            value: attributes.maxDistanceOptions,
                            onChange: function(value) {
                                props.setAttributes({ maxDistanceOptions: value });
                            }
                        }
                    ),
                    attributes.showDistanceFilter && el(
                        SelectControl,
                        {
                            label: 'Distance Unit',
                            value: attributes.defaultDistanceUnit,
                            options: [
                                { label: 'Kilometers', value: 'km' },
                                { label: 'Miles', value: 'mi' }
                            ],
                            onChange: function(value) {
                                props.setAttributes({ defaultDistanceUnit: value });
                            }
                        }
                    ),
                    el(
                        ToggleControl,
                        {
                            label: 'Show "Open Now" Filter',
                            checked: attributes.showOpenNowFilter,
                            onChange: function() {
                                props.setAttributes({ showOpenNowFilter: !attributes.showOpenNowFilter });
                            }
                        }
                    )
                )
            );
            
            // Preview in editor
            return [
                inspectorControls,
                el(
                    'div',
                    { className: props.className },
                    el(
                        ServerSideRender,
                        {
                            block: 'happy-business-listing/advanced-filter',
                            attributes: attributes
                        }
                    )
                )
            ];
        },
        
        // Save function (empty as we're using server-side rendering)
        save: function() {
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
); 