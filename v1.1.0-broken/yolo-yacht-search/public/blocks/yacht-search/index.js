import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('yolo-ys/search-widget', {
    edit: () => {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <div className="yolo-ys-search-widget-preview">
                    <h3>🔍 YOLO Search Widget</h3>
                    <p>The search form will appear here on the frontend.</p>
                    <div className="preview-form">
                        <div className="preview-field">
                            <label>Boat Type</label>
                            <select disabled>
                                <option>Sailing yacht</option>
                                <option>Catamaran</option>
                            </select>
                        </div>
                        <div className="preview-field">
                            <label>Dates</label>
                            <input type="text" placeholder="Select dates..." disabled />
                        </div>
                        <button disabled>SEARCH</button>
                    </div>
                </div>
            </div>
        );
    },
    save: () => {
        return <div className="yolo-ys-search-widget"></div>;
    },
});
