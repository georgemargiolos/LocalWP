<?php
/**
 * YOLO Yacht Search - Analytics & Tracking
 * @since 41.19
 */

if (!defined('ABSPATH')) exit;

class YOLO_YS_Analytics {
    
    private static $instance = null;
    private $ga4_id;
    private $ga4_secret;
    private $fb_pixel_id;
    private $fb_access_token;
    private $debug_mode;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->ga4_id = get_option('yolo_ga4_measurement_id', '');
        $this->ga4_secret = get_option('yolo_ga4_api_secret', '');
        $this->fb_pixel_id = get_option('yolo_fb_pixel_id', '');
        $this->fb_access_token = get_option('yolo_fb_access_token', '');
        $this->debug_mode = get_option('yolo_enable_debug_mode', '0') === '1';
        
        add_action('wp_head', array($this, 'inject_tracking_scripts'), 1);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_analytics_script'));
    }
    
    /**
     * Inject GA4 and Facebook Pixel base scripts
     */
    public function inject_tracking_scripts() {
        if (current_user_can('manage_options') && !$this->debug_mode) return;
        
        // GA4
        if (!empty($this->ga4_id)) { ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($this->ga4_id); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo esc_js($this->ga4_id); ?>');
</script>
        <?php }
        
        // Facebook Pixel
        if (!empty($this->fb_pixel_id)) { ?>
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo esc_js($this->fb_pixel_id); ?>');
fbq('track', 'PageView');
</script>
        <?php }
    }
    
    /**
     * Enqueue client-side analytics JS
     */
    public function enqueue_analytics_script() {
        if (is_admin()) return;
        
        wp_enqueue_script('yolo-analytics', 
            YOLO_YS_PLUGIN_URL . 'public/js/yolo-analytics.js',
            array('jquery'), YOLO_YS_VERSION, true);
        
        wp_localize_script('yolo-analytics', 'yoloAnalyticsConfig', array(
            'ga4_id' => $this->ga4_id,
            'fb_pixel_id' => $this->fb_pixel_id,
            'debug_mode' => $this->debug_mode,
            'currency' => 'EUR',
        ));
    }
    
    /**
     * SERVER-SIDE: Track purchase via both APIs
     * Call from Stripe webhook after successful payment
     */
    public function track_purchase_server_side($data) {
        $fb_success = $this->track_fb_conversions_api($data);
        $ga4_success = $this->track_ga4_measurement_protocol($data);
        
        if ($this->debug_mode) {
            error_log('[YOLO Analytics] Purchase: FB=' . ($fb_success ? 'OK' : 'FAIL') . 
                      ', GA4=' . ($ga4_success ? 'OK' : 'FAIL'));
        }
        return $fb_success || $ga4_success;
    }
    
    /**
     * Facebook Conversions API - Purchase
     */
    private function track_fb_conversions_api($data) {
        if (empty($this->fb_pixel_id) || empty($this->fb_access_token)) return false;
        
        $user_data = array();
        if (!empty($data['customer_email'])) {
            $user_data['em'] = array(hash('sha256', strtolower(trim($data['customer_email']))));
        }
        if (!empty($data['customer_phone'])) {
            $user_data['ph'] = array(hash('sha256', preg_replace('/[^0-9]/', '', $data['customer_phone'])));
        }
        if (!empty($data['client_ip'])) $user_data['client_ip_address'] = $data['client_ip'];
        if (!empty($data['client_user_agent'])) $user_data['client_user_agent'] = $data['client_user_agent'];
        
        $payload = array(
            'data' => array(array(
                'event_name' => 'Purchase',
                'event_time' => time(),
                'event_id' => 'purchase_' . $data['transaction_id'] . '_' . time(),
                'action_source' => 'website',
                'user_data' => $user_data,
                'custom_data' => array(
                    'currency' => $data['currency'] ?? 'EUR',
                    'value' => floatval($data['value']),
                    'content_type' => 'product',
                    'content_ids' => array(strval($data['yacht_id'])),
                    'content_name' => $data['yacht_name'],
                )
            )),
            'access_token' => $this->fb_access_token,
        );
        
        $response = wp_remote_post(
            'https://graph.facebook.com/v18.0/' . $this->fb_pixel_id . '/events',
            array('headers' => array('Content-Type' => 'application/json'),
                  'body' => json_encode($payload), 'timeout' => 30)
        );
        
        return !is_wp_error($response);
    }
    
    /**
     * GA4 Measurement Protocol - Purchase
     */
    private function track_ga4_measurement_protocol($data) {
        if (empty($this->ga4_id) || empty($this->ga4_secret)) return false;
        
        $payload = array(
            'client_id' => $data['ga_client_id'] ?? rand(100000000,999999999) . '.' . time(),
            'events' => array(array(
                'name' => 'purchase',
                'params' => array(
                    'transaction_id' => $data['transaction_id'],
                    'value' => floatval($data['value']),
                    'currency' => $data['currency'] ?? 'EUR',
                    'items' => array(array(
                        'item_id' => strval($data['yacht_id']),
                        'item_name' => $data['yacht_name'],
                        'price' => floatval($data['value']),
                    ))
                )
            ))
        );
        
        $url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . 
               $this->ga4_id . '&api_secret=' . $this->ga4_secret;
        
        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($payload), 'timeout' => 30
        ));
        
        return wp_remote_retrieve_response_code($response) === 204;
    }
}

function yolo_analytics() {
    return YOLO_YS_Analytics::get_instance();
}
