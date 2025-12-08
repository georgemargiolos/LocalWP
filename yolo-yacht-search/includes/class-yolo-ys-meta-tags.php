<?php
/**
 * YOLO Yacht Search - Meta Tags for SEO & Social
 * @since 41.19
 */

if (!defined('ABSPATH')) exit;

class YOLO_YS_Meta_Tags {
    
    private static $instance = null;
    private $current_yacht = null;
    
    public static function get_instance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_head', array($this, 'output_meta_tags'), 5);
        add_action('wp_head', array($this, 'output_schema_json_ld'), 6);
    }
    
    private function is_yacht_page() {
        return isset($_GET['yacht_id']) || isset($_GET['yacht']);
    }
    
    private function get_current_yacht() {
        if ($this->current_yacht !== null) return $this->current_yacht;
        
        $yacht_id = intval($_GET['yacht_id'] ?? $_GET['yacht'] ?? 0);
        if (!$yacht_id) return null;
        
        // Try API cache
        $yachts = get_option('yolo_api_yachts', array());
        foreach ($yachts as $yacht) {
            if (intval($yacht['id'] ?? 0) === $yacht_id) {
                $this->current_yacht = $yacht;
                return $yacht;
            }
        }
        return null;
    }
    
    /**
     * Output Open Graph and Twitter meta tags
     */
    public function output_meta_tags() {
        if (!$this->is_yacht_page()) return;
        $yacht = $this->get_current_yacht();
        if (!$yacht) return;
        
        $name = $yacht['yacht_name'] ?? $yacht['name'] ?? 'Yacht';
        $model = $yacht['yacht_model'] ?? $yacht['model'] ?? '';
        $title = $name . ($model ? ' - ' . $model : '') . ' | ' . get_bloginfo('name');
        $desc = $yacht['description'] ?? sprintf('Charter %s. Available for booking.', $name);
        $image = $yacht['image'] ?? $yacht['main_image'] ?? get_option('yolo_default_og_image', '');
        $price = floatval($yacht['price'] ?? $yacht['min_price'] ?? 0);
        $url = home_url($_SERVER['REQUEST_URI']);
        $twitter = get_option('yolo_twitter_handle', '');
        ?>
<!-- YOLO Open Graph -->
<meta property="og:type" content="product">
<meta property="og:title" content="<?php echo esc_attr($title); ?>">
<meta property="og:description" content="<?php echo esc_attr(wp_trim_words(strip_tags($desc), 30)); ?>">
<meta property="og:url" content="<?php echo esc_url($url); ?>">
<meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
<?php if ($image): ?><meta property="og:image" content="<?php echo esc_url($image); ?>"><?php endif; ?>
<?php if ($price): ?>
<meta property="product:price:amount" content="<?php echo esc_attr($price); ?>">
<meta property="product:price:currency" content="EUR">
<?php endif; ?>

<!-- YOLO Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr($title); ?>">
<meta name="twitter:description" content="<?php echo esc_attr(wp_trim_words(strip_tags($desc), 20)); ?>">
<?php if ($image): ?><meta name="twitter:image" content="<?php echo esc_url($image); ?>"><?php endif; ?>
<?php if ($twitter): ?><meta name="twitter:site" content="<?php echo esc_attr($twitter); ?>"><?php endif; ?>
        <?php
    }
    
    /**
     * Output Schema.org JSON-LD
     */
    public function output_schema_json_ld() {
        if (!get_option('yolo_enable_schema', '1')) return;
        if (!$this->is_yacht_page()) return;
        $yacht = $this->get_current_yacht();
        if (!$yacht) return;
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => ($yacht['yacht_name'] ?? '') . ' ' . ($yacht['yacht_model'] ?? ''),
            'description' => $yacht['description'] ?? 'Charter yacht available.',
            'url' => home_url($_SERVER['REQUEST_URI']),
            'category' => 'Yacht Charter',
        );
        
        if ($image = $yacht['image'] ?? $yacht['main_image'] ?? '') {
            $schema['image'] = array($image);
        }
        
        if ($price = floatval($yacht['price'] ?? $yacht['min_price'] ?? 0)) {
            $schema['offers'] = array(
                '@type' => 'Offer',
                'priceCurrency' => 'EUR',
                'price' => $price,
                'availability' => 'https://schema.org/InStock',
            );
        }
        ?>
<script type="application/ld+json">
<?php echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
</script>
        <?php
    }
}

function yolo_meta_tags() {
    return YOLO_YS_Meta_Tags::get_instance();
}
