<?php
/**
 * YOLO Yacht Search - Meta Tags for SEO & Social
 * Version: 41.23 (Fixed)
 * 
 * FIXES APPLIED:
 * 1. Changed from API cache to database queries (matches yacht-details-v3.php)
 * 2. Fixed yacht ID handling (uses string, not intval - prevents precision loss)
 * 3. Added complete Open Graph tags with image dimensions
 * 4. Added complete Twitter Card tags
 * 5. Added standard meta description tag
 * 6. Added complete JSON-LD Product schema with AggregateOffer
 * 7. Added complete JSON-LD Offer schemas with dates
 * 8. Added JSON-LD Place schema for location
 * 
 * REPLACE: yolo-yacht-search/includes/class-yolo-ys-meta-tags.php
 * 
 * Priority 1 (Critical): Open Graph, Twitter Card, Meta Description
 * Priority 2 (High): JSON-LD structured data (Product, Offer, Place schemas)
 */

if (!defined('ABSPATH')) exit;

class YOLO_YS_Meta_Tags {
    
    private static $instance = null;
    private $current_yacht = null;
    private $current_images = null;
    private $current_prices = null;
    
    public static function get_instance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_head', array($this, 'output_meta_description'), 1);
        add_action('wp_head', array($this, 'output_meta_tags'), 5);
        add_action('wp_head', array($this, 'output_schema_json_ld'), 6);
        add_filter('pre_get_document_title', array($this, 'filter_yacht_page_title'), 10);
        add_filter('wp_title', array($this, 'filter_yacht_page_title'), 10, 2);
    }
    
    private function is_yacht_page() {
        return isset($_GET['yacht_id']) || isset($_GET['yacht']) || get_query_var('yacht_slug', '');
    }
    
    /**
     * Get yacht data from database (same as template)
     * CRITICAL: yacht_id is STRING, not int - large IDs lose precision with intval
     */
    private function get_current_yacht() {
        if ($this->current_yacht !== null) return $this->current_yacht;
        
        global $wpdb;
        $yacht_table = $wpdb->prefix . 'yolo_yachts';
        
        // Check for pretty URL slug first
        $yacht_slug = get_query_var('yacht_slug', '');
        if (!empty($yacht_slug)) {
            $yacht = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $yacht_table WHERE slug = %s",
                $yacht_slug
            ), ARRAY_A);
            
            if ($yacht) {
                $this->current_yacht = $yacht;
                $yacht_id = $yacht['id'];
                
                // Get images
                $images_table = $wpdb->prefix . 'yolo_yacht_images';
                $this->current_images = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $images_table WHERE yacht_id = %s ORDER BY sort_order ASC LIMIT 10",
                    $yacht_id
                ), ARRAY_A);
                
                return $this->current_yacht;
            }
        }
        
        // Fallback to yacht_id parameter
        $yacht_id = isset($_GET['yacht_id']) ? sanitize_text_field($_GET['yacht_id']) : 
                    (isset($_GET['yacht']) ? sanitize_text_field($_GET['yacht']) : '');
        if (empty($yacht_id)) return null;
        
        // Get yacht from database (same method as yacht-details-v3.php)
        $yacht = $wpdb->get_row($wpdb->prepare("SELECT * FROM $yacht_table WHERE id = %s", $yacht_id), ARRAY_A);
        
        if (!$yacht) return null;
        
        $this->current_yacht = $yacht;
        
        // Get images
        $images_table = $wpdb->prefix . 'yolo_yacht_images';
        $this->current_images = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $images_table WHERE yacht_id = %s ORDER BY sort_order ASC LIMIT 10",
            $yacht_id
        ), ARRAY_A);
        
        // Get prices (for Offer schema)
        $this->current_prices = YOLO_YS_Database_Prices::get_yacht_prices($yacht_id, 52);
        
        return $yacht;
    }
    
    /**
     * Output standard meta description tag
     */
    public function output_meta_description() {
        if (!$this->is_yacht_page()) return;
        $yacht = $this->get_current_yacht();
        if (!$yacht) return;
        
        $name = $yacht['name'] ?? 'Yacht';
        $model = $yacht['model'] ?? '';
        $description = $yacht['description'] ?? '';
        
        // Build description
        $meta_desc = '';
        if ($description) {
            $meta_desc = wp_trim_words(strip_tags($description), 25);
        } else {
            $meta_desc = sprintf('Charter %s%s. Available for booking in the Ionian Sea, Greece.', 
                $name, 
                $model ? ' - ' . $model : ''
            );
        }
        
        echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "\n";
    }
    
    /**
     * Output Open Graph and Twitter Card meta tags
     */
    public function output_meta_tags() {
        if (!$this->is_yacht_page()) return;
        $yacht = $this->get_current_yacht();
        if (!$yacht) return;
        
        $name = $yacht['name'] ?? 'Yacht';
        $model = $yacht['model'] ?? '';
        $description = $yacht['description'] ?? '';
        $home_base = $yacht['home_base'] ?? 'Preveza Main Port, Greece';
        
        // Build title
        $title = $name;
        if ($model) $title .= ' | ' . $model;
        $title .= ' – Yolo Charters';
        
        // Build description
        $desc = '';
        if ($description) {
            $desc = wp_trim_words(strip_tags($description), 30);
        } else {
            $desc = sprintf('Charter %s%s. Available for booking in %s.', 
                $name, 
                $model ? ' - ' . $model : '',
                $home_base
            );
        }
        
        // Get first image
        $image = '';
        if (!empty($this->current_images) && isset($this->current_images[0]['url'])) {
            $image = $this->current_images[0]['url'];
        } elseif (!empty($this->current_images) && isset($this->current_images[0]['image_url'])) {
            $image = $this->current_images[0]['image_url'];
        }
        
        // Get current URL
        $url = home_url($_SERVER['REQUEST_URI']);
        
        // Get minimum price
        $min_price = 0;
        if (!empty($this->current_prices)) {
            $prices_array = array_map(function($p) {
                return floatval($p->price ?? $p->start_price ?? 0);
            }, $this->current_prices);
            $prices_array = array_filter($prices_array, function($p) { return $p > 0; });
            if (!empty($prices_array)) {
                $min_price = min($prices_array);
            }
        }
        
        $twitter_handle = get_option('yolo_twitter_handle', '');
        ?>
<!-- YOLO SEO Meta Tags v41.23 -->
<!-- Open Graph / Facebook -->
<meta property="og:type" content="product">
<meta property="og:title" content="<?php echo esc_attr($title); ?>">
<meta property="og:description" content="<?php echo esc_attr($desc); ?>">
<meta property="og:url" content="<?php echo esc_url($url); ?>">
<meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
<?php if ($image): ?>
<meta property="og:image" content="<?php echo esc_url($image); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?php echo esc_attr($name . ($model ? ' - ' . $model : '')); ?>">
<?php endif; ?>
<?php if ($min_price > 0): ?>
<meta property="product:price:amount" content="<?php echo esc_attr($min_price); ?>">
<meta property="product:price:currency" content="EUR">
<?php endif; ?>
<meta property="product:category" content="Yacht Charter">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr($title); ?>">
<meta name="twitter:description" content="<?php echo esc_attr(wp_trim_words(strip_tags($desc), 20)); ?>">
<?php if ($image): ?>
<meta name="twitter:image" content="<?php echo esc_url($image); ?>">
<meta name="twitter:image:alt" content="<?php echo esc_attr($name . ($model ? ' - ' . $model : '')); ?>">
<?php endif; ?>
<?php if ($twitter_handle): ?>
<meta name="twitter:site" content="<?php echo esc_attr($twitter_handle); ?>">
<?php endif; ?>
        <?php
    }
    
    /**
     * Output Schema.org JSON-LD structured data
     * Includes: Product, Offer, and Place schemas
     */
    public function output_schema_json_ld() {
        if (!get_option('yolo_enable_schema', '1')) return;
        if (!$this->is_yacht_page()) return;
        $yacht = $this->get_current_yacht();
        if (!$yacht) return;
        
        $name = $yacht['name'] ?? 'Yacht';
        $model = $yacht['model'] ?? '';
        $description = $yacht['description'] ?? '';
        $home_base = $yacht['home_base'] ?? 'Preveza Main Port';
        $length = floatval($yacht['length'] ?? 0);
        $beam = floatval($yacht['beam'] ?? 0);
        $draft = floatval($yacht['draft'] ?? 0);
        $year_built = intval($yacht['year_built'] ?? 0);
        $cabins = intval($yacht['cabins'] ?? 0);
        $berths = intval($yacht['berths'] ?? 0);
        $wc = intval($yacht['wc'] ?? 0);
        
        $url = home_url($_SERVER['REQUEST_URI']);
        
        // Get images
        $images = array();
        if (!empty($this->current_images)) {
            foreach ($this->current_images as $img) {
                $img_url = $img['url'] ?? $img['image_url'] ?? '';
                if ($img_url) {
                    $images[] = $img_url;
                }
            }
        }
        
        // Get offers from prices
        $offers = array();
        if (!empty($this->current_prices)) {
            $today = date('Y-m-d');
            foreach ($this->current_prices as $price) {
                if ($price->date_from >= $today) {
                    $price_value = floatval($price->price ?? $price->start_price ?? 0);
                    if ($price_value > 0) {
                        $offers[] = array(
                            '@type' => 'Offer',
                            'priceCurrency' => $price->currency ?? 'EUR',
                            'price' => number_format($price_value, 2, '.', ''),
                            'availability' => 'https://schema.org/InStock',
                            'url' => $url,
                            'priceValidUntil' => date('Y-m-d', strtotime($price->date_to . ' +1 year')),
                            'validFrom' => $price->date_from,
                            'validThrough' => $price->date_to,
                        );
                    }
                }
            }
        }
        
        // Build Product schema
        $product_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name . ($model ? ' - ' . $model : ''),
            'description' => $description ? wp_trim_words(strip_tags($description), 50) : 
                            sprintf('Charter yacht %s%s available for booking in the Ionian Sea, Greece.', $name, $model ? ' - ' . $model : ''),
            'url' => $url,
            'category' => 'Yacht Charter',
            'brand' => array(
                '@type' => 'Brand',
                'name' => get_bloginfo('name')
            ),
        );
        
        // Add images
        if (!empty($images)) {
            $product_schema['image'] = $images;
        }
        
        // Add offers
        if (!empty($offers)) {
            // Use AggregateOffer if multiple offers
            if (count($offers) > 1) {
                $min_price = min(array_map(function($o) { return floatval($o['price']); }, $offers));
                $max_price = max(array_map(function($o) { return floatval($o['price']); }, $offers));
                
                $product_schema['offers'] = array(
                    '@type' => 'AggregateOffer',
                    'priceCurrency' => 'EUR',
                    'lowPrice' => number_format($min_price, 2, '.', ''),
                    'highPrice' => number_format($max_price, 2, '.', ''),
                    'offerCount' => count($offers),
                    'offers' => array_slice($offers, 0, 5), // Limit to 5 offers
                );
            } else {
                $product_schema['offers'] = $offers[0];
            }
        }
        
        // Add additional properties
        $additional_properties = array();
        if ($length > 0) {
            $additional_properties[] = array(
                '@type' => 'PropertyValue',
                'name' => 'Length',
                'value' => number_format($length, 2) . ' m'
            );
        }
        if ($year_built > 0) {
            $additional_properties[] = array(
                '@type' => 'PropertyValue',
                'name' => 'Year Built',
                'value' => (string)$year_built
            );
        }
        if ($cabins > 0) {
            $additional_properties[] = array(
                '@type' => 'PropertyValue',
                'name' => 'Cabins',
                'value' => (string)$cabins
            );
        }
        if ($berths > 0) {
            $additional_properties[] = array(
                '@type' => 'PropertyValue',
                'name' => 'Berths',
                'value' => (string)$berths
            );
        }
        if (!empty($additional_properties)) {
            $product_schema['additionalProperty'] = $additional_properties;
        }
        
        // Build Place schema for location
        $place_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Place',
            'name' => $home_base,
            'address' => array(
                '@type' => 'PostalAddress',
                'addressLocality' => $home_base,
                'addressCountry' => 'GR',
                'addressRegion' => 'Ionian Islands'
            ),
            'geo' => array(
                '@type' => 'GeoCoordinates',
                'latitude' => '38.9600', // Preveza approximate coordinates
                'longitude' => '20.7500'
            )
        );
        
        // Output schemas
        ?>
<script type="application/ld+json">
<?php echo json_encode($product_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>

<script type="application/ld+json">
<?php echo json_encode($place_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>
        <?php
    }
    
    /**
     * Filter WordPress page title for yacht details pages
     */
    public function filter_yacht_page_title($title, $sep = null) {
        if (!$this->is_yacht_page()) {
            return $title;
        }
        
        $yacht = $this->get_current_yacht();
        if (empty($yacht)) {
            return $title;
        }
        
        $name = $yacht['name'] ?? 'Yacht';
        $model = $yacht['model'] ?? '';
        
        $new_title = $name;
        if ($model) {
            $new_title .= ' | ' . $model;
        }
        $new_title .= ' – Yolo Charters';
        
        return $new_title;
    }
}

function yolo_meta_tags() {
    return YOLO_YS_Meta_Tags::get_instance();
}

