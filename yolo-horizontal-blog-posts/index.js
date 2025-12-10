/**
 * YOLO Horizontal Blog Posts Block
 * Block registration with RangeControl for post count
 */

(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, RangeControl } = wp.components;
    const { createElement: el } = wp.element;
    const { __ } = wp.i18n;

    registerBlockType('yolo-blog-posts/horizontal-blog-posts', {
        title: __('YOLO Horizontal Blog Posts', 'yolo-horizontal-blog-posts'),
        icon: 'admin-post',
        category: 'widgets',
        attributes: {
            postCount: {
                type: 'number',
                default: 10
            }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { postCount } = attributes;

            return el('div', { className: 'yolo-horizontal-blog-posts-editor' },
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'yolo-horizontal-blog-posts'), initialOpen: true },
                        el(RangeControl, {
                            label: __('Number of Posts', 'yolo-horizontal-blog-posts'),
                            value: postCount,
                            onChange: function(value) {
                                setAttributes({ postCount: value });
                            },
                            min: 1,
                            max: 20
                        })
                    )
                ),
                el('div', { className: 'yolo-horizontal-blog-posts-placeholder' },
                    el('p', { style: { textAlign: 'center', padding: '20px', background: '#f0f0f0', borderRadius: '8px' } },
                        __('YOLO Horizontal Blog Posts', 'yolo-horizontal-blog-posts'),
                        el('br'),
                        el('small', {}, __('Displaying ' + postCount + ' posts', 'yolo-horizontal-blog-posts'))
                    )
                )
            );
        },
        save: function() {
            return null; // Server-side rendering
        }
    });
})(window.wp);
