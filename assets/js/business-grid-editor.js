(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { ToggleControl, PanelBody, SelectControl, RangeControl } = wp.components;
    const { InspectorControls } = wp.blockEditor || wp.editor;
    const { createElement: el, Fragment } = wp.element;
    const { __ } = wp.i18n;

    registerBlockType('happy-business-listing/business-grid', {
        title: __('Business Grid', 'happy-business-listing'),
        icon: 'store',
        category: 'happy-business-listing',
        description: __('Display a grid of business listings with optional filters.', 'happy-business-listing'),
        keywords: [
            __('business', 'happy-business-listing'),
            __('directory', 'happy-business-listing'),
            __('listings', 'happy-business-listing')
        ],
        attributes: {
            columns: { type: 'number', default: 3 },
            postsPerPage: { type: 'number', default: 12 },
            showFilters: { type: 'boolean', default: true },
            showSearch: { type: 'boolean', default: true },
            showPagination: { type: 'boolean', default: true },
            filterStyle: { type: 'string', default: 'default' },
            gridStyle: { type: 'string', default: 'cards' },
            showExcerpt: { type: 'boolean', default: true },
            showRating: { type: 'boolean', default: true },
            showLocation: { type: 'boolean', default: true },
            showCompanyType: { type: 'boolean', default: true },
            showVerificationBadge: { type: 'boolean', default: true },
            orderBy: { type: 'string', default: 'date' },
            order: { type: 'string', default: 'DESC' },
            categories: { type: 'array', default: [] },
            excludeCategories: { type: 'array', default: [] },
            className: { type: 'string' }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;

            const layoutOptions = [
                { label: __('Cards', 'happy-business-listing'), value: 'cards' },
                { label: __('List', 'happy-business-listing'), value: 'list' },
                { label: __('Grid', 'happy-business-listing'), value: 'grid' },
                { label: __('Masonry', 'happy-business-listing'), value: 'masonry' }
            ];

            const filterStyleOptions = [
                { label: __('Default', 'happy-business-listing'), value: 'default' },
                { label: __('Minimal', 'happy-business-listing'), value: 'minimal' },
                { label: __('Boxed', 'happy-business-listing'), value: 'boxed' },
                { label: __('Modern', 'happy-business-listing'), value: 'modern' }
            ];

            const orderByOptions = [
                { label: __('Date', 'happy-business-listing'), value: 'date' },
                { label: __('Title', 'happy-business-listing'), value: 'title' },
                { label: __('Rating', 'happy-business-listing'), value: 'rating' },
                { label: __('Random', 'happy-business-listing'), value: 'rand' }
            ];

            const orderOptions = [
                { label: __('Descending', 'happy-business-listing'), value: 'DESC' },
                { label: __('Ascending', 'happy-business-listing'), value: 'ASC' }
            ];

            return el(
                Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: __('Layout Settings', 'happy-business-listing'), initialOpen: true },
                        el(RangeControl, {
                            label: __('Columns', 'happy-business-listing'),
                            value: attributes.columns,
                            onChange: function(value) { setAttributes({ columns: value }); },
                            min: 1,
                            max: 6,
                            step: 1
                        }),
                        el(RangeControl, {
                            label: __('Posts Per Page', 'happy-business-listing'),
                            value: attributes.postsPerPage,
                            onChange: function(value) { setAttributes({ postsPerPage: value }); },
                            min: 1,
                            max: 50,
                            step: 1
                        }),
                        el(SelectControl, {
                            label: __('Grid Style', 'happy-business-listing'),
                            value: attributes.gridStyle,
                            options: layoutOptions,
                            onChange: function(value) { setAttributes({ gridStyle: value }); }
                        })
                    ),
                    el(
                        PanelBody,
                        { title: __('Filter Settings', 'happy-business-listing'), initialOpen: false },
                        el(ToggleControl, {
                            label: __('Show Filters', 'happy-business-listing'),
                            checked: attributes.showFilters,
                            onChange: function(value) { setAttributes({ showFilters: value }); }
                        }),
                        el(ToggleControl, {
                            label: __('Show Search', 'happy-business-listing'),
                            checked: attributes.showSearch,
                            onChange: function(value) { setAttributes({ showSearch: value }); }
                        }),
                        el(SelectControl, {
                            label: __('Filter Style', 'happy-business-listing'),
                            value: attributes.filterStyle,
                            options: filterStyleOptions,
                            onChange: function(value) { setAttributes({ filterStyle: value }); }
                        })
                    ),
                    el(
                        PanelBody,
                        { title: __('Display Options', 'happy-business-listing'), initialOpen: false },
                        el(ToggleControl, {
                            label: __('Show Excerpt', 'happy-business-listing'),
                            checked: attributes.showExcerpt,
                            onChange: function(value) { setAttributes({ showExcerpt: value }); }
                        }),
                        el(ToggleControl, {
                            label: __('Show Rating', 'happy-business-listing'),
                            checked: attributes.showRating,
                            onChange: function(value) { setAttributes({ showRating: value }); }
                        }),
                        el(ToggleControl, {
                            label: __('Show Location', 'happy-business-listing'),
                            checked: attributes.showLocation,
                            onChange: function(value) { setAttributes({ showLocation: value }); }
                        }),
                        el(ToggleControl, {
                            label: __('Show Company Type', 'happy-business-listing'),
                            checked: attributes.showCompanyType,
                            onChange: function(value) { setAttributes({ showCompanyType: value }); }
                        }),
                        el(ToggleControl, {
                            label: __('Show Verification Badge', 'happy-business-listing'),
                            checked: attributes.showVerificationBadge,
                            onChange: function(value) { setAttributes({ showVerificationBadge: value }); }
                        }),
                        el(ToggleControl, {
                            label: __('Show Pagination', 'happy-business-listing'),
                            checked: attributes.showPagination,
                            onChange: function(value) { setAttributes({ showPagination: value }); }
                        })
                    ),
                    el(
                        PanelBody,
                        { title: __('Sorting', 'happy-business-listing'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Order By', 'happy-business-listing'),
                            value: attributes.orderBy,
                            options: orderByOptions,
                            onChange: function(value) { setAttributes({ orderBy: value }); }
                        }),
                        el(SelectControl, {
                            label: __('Order', 'happy-business-listing'),
                            value: attributes.order,
                            options: orderOptions,
                            onChange: function(value) { setAttributes({ order: value }); }
                        })
                    )
                ),
                el(
                    'div',
                    { 
                        className: 'hbl-business-grid-preview',
                        style: { 
                            padding: '20px', 
                            border: '2px dashed #ddd', 
                            borderRadius: '8px',
                            backgroundColor: '#f9f9f9',
                            textAlign: 'center'
                        }
                    },
                    el('div', { 
                        style: { 
                            fontSize: '48px', 
                            marginBottom: '16px',
                            color: '#666'
                        } 
                    }, '🏪'),
                    el('h3', { 
                        style: { 
                            margin: '0 0 8px 0',
                            color: '#333'
                        } 
                    }, __('Business Grid', 'happy-business-listing')),
                    el('p', { 
                        style: { 
                            margin: '0 0 16px 0',
                            color: '#666'
                        } 
                    }, __('This block will display a grid of business listings on the frontend.', 'happy-business-listing')),
                    el('div', {
                        style: {
                            display: 'flex',
                            justifyContent: 'center',
                            gap: '20px',
                            fontSize: '14px',
                            color: '#888'
                        }
                    },
                        el('span', {}, __('Columns:', 'happy-business-listing') + ' ' + attributes.columns),
                        el('span', {}, __('Posts:', 'happy-business-listing') + ' ' + attributes.postsPerPage),
                        el('span', {}, __('Style:', 'happy-business-listing') + ' ' + attributes.gridStyle)
                    ),
                    attributes.showFilters && el('div', {
                        style: {
                            marginTop: '12px',
                            padding: '8px 12px',
                            backgroundColor: '#e3f2fd',
                            borderRadius: '4px',
                            fontSize: '12px',
                            color: '#1976d2'
                        }
                    }, __('✓ Filters enabled', 'happy-business-listing'))
                )
            );
        },
        save: function() {
            return null; // Rendered via PHP
        }
    });
})(window.wp); 