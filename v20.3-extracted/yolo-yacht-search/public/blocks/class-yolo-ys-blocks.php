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
    }
}
