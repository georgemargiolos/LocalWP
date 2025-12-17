<?php
/**
 * Register Gutenberg blocks
 */
class YOLO_YS_Blocks {
    
    public function __construct() {
        // Constructor
    }
    
    /**
     * Register blocks
     */
    public function register_blocks() {
        // Register Search Widget Block
        register_block_type(YOLO_YS_PLUGIN_DIR . 'public/blocks/yacht-search');
        
        // Register Search Results Block
        register_block_type(YOLO_YS_PLUGIN_DIR . 'public/blocks/yacht-results');
        
        // Register Horizontal Yacht Cards Block
        register_block_type(YOLO_YS_PLUGIN_DIR . 'public/blocks/yacht-horizontal-cards', array(
            'render_callback' => array($this, 'render_horizontal_yacht_cards')
        ));
    }
    
    /**
     * Render callback for horizontal yacht cards block
     */
    public function render_horizontal_yacht_cards($attributes, $content) {
        ob_start();
        include YOLO_YS_PLUGIN_DIR . 'public/blocks/yacht-horizontal-cards/render.php';
        return ob_get_clean();
    }
}
