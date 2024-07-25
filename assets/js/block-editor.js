(function (blocks, element, editor, components, i18n, apiFetch) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;

    registerBlockType('hbl/business-block', {
        title: i18n.__('Business Listing'),
        icon: 'store',
        category: 'widgets',
        edit: function (props) {
            return el(
                'div',
                { className: props.className },
                'Edit Business Listing Block'
            );
        },
        save: function () {
            return el(
                'div',
                null,
                'Save Business Listing Block'
            );
        },
    });
})(window.wp.blocks, window.wp.element, window.wp.editor, window.wp.components, window.wp.i18n, window.wp.apiFetch);
