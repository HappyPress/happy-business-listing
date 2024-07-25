(function (blocks, element) {
    var el = element.createElement;
    blocks.registerBlockType('hbl/business-block', {
        title: 'Business Listing',
        icon: 'store',
        category: 'widgets',
        edit: function (props) {
            return el(
                'div',
                { className: props.className },
                'Hello, Business Listing Block!'
            );
        },
        save: function () {
            return el(
                'div',
                null,
                'Hello, Business Listing Block!'
            );
        },
    });
})(window.wp.blocks, window.wp.element);
