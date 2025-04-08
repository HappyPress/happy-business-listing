/**
 * Gutenberg Block Editor Extensions for Happy Business Listing
 */
(function(wp) {
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginSidebar = wp.editPost.PluginSidebar;
    var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
    var el = wp.element.createElement;
    var __ = wp.i18n.__;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    var SelectControl = wp.components.SelectControl;
    var ToggleControl = wp.components.ToggleControl;
    var withSelect = wp.data.withSelect;
    var withDispatch = wp.data.withDispatch;
    var compose = wp.compose.compose;
    
    // Only load for business_listing post type
    var currentPostType = wp.data.select('core/editor').getCurrentPostType();
    if (currentPostType !== 'business_listing') {
        return;
    }
    
    // Create sidebar component
    var BusinessListingSidebar = function(props) {
        return el(
            'div',
            { className: 'hbl-sidebar-panel' },
            el(
                PanelBody,
                {
                    title: __('Business Details', 'happy-business-listing'),
                    initialOpen: true
                },
                el(
                    SelectControl,
                    {
                        label: __('Company Type', 'happy-business-listing'),
                        value: props.meta.company_type || '',
                        options: [
                            { label: __('Select Type', 'happy-business-listing'), value: '' },
                            { label: __('Pvt Ltd', 'happy-business-listing'), value: 'Pvt Ltd' },
                            { label: __('LLP', 'happy-business-listing'), value: 'LLP' },
                            { label: __('OPC', 'happy-business-listing'), value: 'OPC' },
                            { label: __('Other', 'happy-business-listing'), value: 'Other' }
                        ],
                        onChange: function(value) {
                            props.updateMeta({ company_type: value });
                        }
                    }
                ),
                el(
                    TextControl,
                    {
                        label: __('GST No.', 'happy-business-listing'),
                        value: props.meta.gst_no || '',
                        onChange: function(value) {
                            props.updateMeta({ gst_no: value });
                        }
                    }
                ),
                el(
                    TextControl,
                    {
                        label: __('TAN/PAN', 'happy-business-listing'),
                        value: props.meta.tan_pan || '',
                        onChange: function(value) {
                            props.updateMeta({ tan_pan: value });
                        }
                    }
                ),
                el(
                    TextControl,
                    {
                        label: __('Location', 'happy-business-listing'),
                        value: props.meta.location || '',
                        onChange: function(value) {
                            props.updateMeta({ location: value });
                        }
                    }
                ),
                el(
                    TextControl,
                    {
                        label: __('Website', 'happy-business-listing'),
                        type: 'url',
                        value: props.meta.website || '',
                        onChange: function(value) {
                            props.updateMeta({ website: value });
                        }
                    }
                ),
                el(
                    TextControl,
                    {
                        label: __('Social Media', 'happy-business-listing'),
                        value: props.meta.social_media || '',
                        onChange: function(value) {
                            props.updateMeta({ social_media: value });
                        }
                    }
                ),
                el(
                    TextControl,
                    {
                        label: __('WhatsApp Number', 'happy-business-listing'),
                        value: props.meta.whatsapp_number || '',
                        onChange: function(value) {
                            props.updateMeta({ whatsapp_number: value });
                        }
                    }
                )
            ),
            el(
                PanelBody,
                {
                    title: __('Business Options', 'happy-business-listing'),
                    initialOpen: false
                },
                el(
                    ToggleControl,
                    {
                        label: __('Featured Business', 'happy-business-listing'),
                        checked: props.meta.is_featured === '1',
                        onChange: function() {
                            props.updateMeta({ is_featured: props.meta.is_featured === '1' ? '0' : '1' });
                        }
                    }
                ),
                el(
                    SelectControl,
                    {
                        label: __('Verification Status', 'happy-business-listing'),
                        value: props.meta.verification_status || 'pending',
                        options: [
                            { label: __('Pending', 'happy-business-listing'), value: 'pending' },
                            { label: __('Verified', 'happy-business-listing'), value: 'verified' },
                            { label: __('Rejected', 'happy-business-listing'), value: 'rejected' }
                        ],
                        onChange: function(value) {
                            props.updateMeta({ verification_status: value });
                        }
                    }
                )
            )
        );
    };
    
    // Connect component to WordPress data
    var BusinessListingSidebarWithData = compose([
        withSelect(function(select) {
            return {
                meta: select('core/editor').getEditedPostAttribute('meta') || {}
            };
        }),
        withDispatch(function(dispatch) {
            return {
                updateMeta: function(metaData) {
                    dispatch('core/editor').editPost({ meta: metaData });
                }
            };
        })
    ])(BusinessListingSidebar);
    
    // Register the plugin
    registerPlugin('hbl-business-sidebar', {
        render: function() {
            return el(
                'div',
                {},
                el(
                    PluginSidebarMoreMenuItem,
                    {
                        target: 'hbl-business-sidebar'
                    },
                    __('Business Details', 'happy-business-listing')
                ),
                el(
                    PluginSidebar,
                    {
                        name: 'hbl-business-sidebar',
                        title: __('Business Details', 'happy-business-listing')
                    },
                    el(BusinessListingSidebarWithData, {})
                )
            );
        }
    });
})(window.wp);