import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('yolo-ys/search-results', {
    edit: () => {
        const blockProps = useBlockProps();
        return (
            <div {...blockProps}>
                <div className="yolo-ys-results-preview">
                    <h3>📋 YOLO Search Results</h3>
                    <p>Search results will appear here when users submit the search form.</p>
                    <div className="preview-results">
                        <div className="preview-result-card yolo-boat">
                            <div className="badge">YOLO Boat</div>
                            <h4>Example Yacht 1</h4>
                            <p>Company: YOLO Charters (7850)</p>
                            <p className="price">€2,500 / week</p>
                        </div>
                        <div className="preview-result-card">
                            <h4>Example Yacht 2</h4>
                            <p>Company: Friend Company</p>
                            <p className="price">€2,200 / week</p>
                        </div>
                    </div>
                </div>
            </div>
        );
    },
    save: () => {
        return <div className="yolo-ys-search-results"></div>;
    },
});
