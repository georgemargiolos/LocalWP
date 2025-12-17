/**
 * YOLO Horizontal Yacht Cards Block
 * Displays YOLO company yachts in horizontal layout with image carousel
 */

(function (blocks, element, serverSideRender) {
    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var ServerSideRender = serverSideRender;

    registerBlockType('yolo-ys/horizontal-yacht-cards', {
        edit: function () {
            return el(
                'div',
                { className: 'yolo-ys-block-placeholder' },
                el('p', {}, '🚤 YOLO Horizontal Yacht Cards'),
                el('p', { style: { fontSize: '14px', color: '#666' } }, 'Displays YOLO company yachts with image carousel')
            );
        },
        save: function () {
            return null; // Dynamic block - rendered server-side
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.serverSideRender
);
