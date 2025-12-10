/**
 * YOLO Horizontal Yacht Cards Block
 */

(function() {
    var el = wp.element.createElement;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var registerBlockType = wp.blocks.registerBlockType;

    registerBlockType('yolo-hyc/horizontal-yacht-cards', {
        edit: function(props) {
            var blockProps = useBlockProps({
                className: 'yolo-hyc-block-editor-preview'
            });
            
            return el(
                'div',
                blockProps,
                el('div', { 
                    style: { 
                        padding: '40px 20px', 
                        textAlign: 'center', 
                        backgroundColor: '#f0f7ff',
                        borderRadius: '12px',
                        border: '2px dashed #0073aa'
                    } 
                },
                    el('span', { style: { fontSize: '48px', display: 'block', marginBottom: '10px' } }, '🚤'),
                    el('h3', { style: { margin: '0 0 8px', color: '#0073aa' } }, 'YOLO Horizontal Yacht Cards'),
                    el('p', { style: { margin: 0, color: '#666', fontSize: '14px' } }, 'Displays YOLO fleet yachts with image carousel on the frontend.')
                )
            );
        },
        
        save: function() {
            return null;
        }
    });
})();
