<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
/**
 * Guest Login Form Template
 * 
 * @package YOLO_Yacht_Search
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle login form submission
$login_error = '';
$login_success = '';

if (isset($_POST['yolo_guest_login_submit'])) {
    // Verify nonce
    if (!isset($_POST['yolo_guest_login_nonce']) || !wp_verify_nonce($_POST['yolo_guest_login_nonce'], 'yolo_guest_login')) {
        $login_error = 'Security check failed. Please try again.';
    } else {
        $username = sanitize_text_field($_POST['yolo_username']);
        $password = $_POST['yolo_password'];
        $remember = isset($_POST['yolo_remember']);
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, false);
        
        if (is_wp_error($user)) {
            $login_error = $user->get_error_message();
        } else {
            // Check if user is a guest
            if (in_array('guest', (array) $user->roles)) {
                // Redirect to guest dashboard
                $dashboard_page = get_page_by_path('guest-dashboard');
                if ($dashboard_page) {
                    wp_redirect(get_permalink($dashboard_page->ID));
                } else {
                    wp_redirect(home_url());
                }
                exit;
            } else {
                // Not a guest user
                wp_logout();
                $login_error = 'This login page is for guests only. Please use the regular login page.';
            }
        }
    }
}
?>

<div class="container py-5">
<div class="yolo-guest-login-container">
    <div class="yolo-guest-login-box">
        <div class="yolo-guest-login-header">
            <h2>Guest Login</h2>
            <p>Access your bookings and upload your sailing license</p>
        </div>
        
        <?php if ($login_error): ?>
            <div class="yolo-login-error">
                <p><?php echo wp_kses_post($login_error); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($login_success): ?>
            <div class="yolo-login-success">
                <p><?php echo esc_html($login_success); ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" class="yolo-guest-login-form">
            <?php wp_nonce_field('yolo_guest_login', 'yolo_guest_login_nonce'); ?>
            
            <div class="yolo-form-group">
                <label for="yolo_username">Email Address</label>
                <input 
                    type="text" 
                    id="yolo_username" 
                    name="yolo_username" 
                    class="yolo-form-control" 
                    placeholder="Enter your email"
                    required
                    value="<?php echo isset($_POST['yolo_username']) ? esc_attr($_POST['yolo_username']) : ''; ?>"
                />
            </div>
            
            <div class="yolo-form-group">
                <label for="yolo_password">Password</label>
                <input 
                    type="password" 
                    id="yolo_password" 
                    name="yolo_password" 
                    class="yolo-form-control" 
                    placeholder="Enter your password"
                    required
                />
                <p class="yolo-form-hint">Your password is: [booking_id]YoLo (e.g., 5YoLo for booking #5)</p>
            </div>
            
            <div class="yolo-form-group yolo-form-checkbox">
                <label>
                    <input type="checkbox" name="yolo_remember" value="1" />
                    Remember me
                </label>
            </div>
            
            <div class="yolo-form-group">
                <button type="submit" name="yolo_guest_login_submit" class="yolo-btn yolo-btn-primary">
                    Log In
                </button>
            </div>
            
            <div class="yolo-login-footer">
                <p>
                    <a href="<?php echo wp_lostpassword_url(get_permalink()); ?>">Forgot your password?</a>
                </p>
                <p class="yolo-login-help">
                    Need help? Contact us at <a href="mailto:info@yolo-charters.com">info@yolo-charters.com</a> 
                    or call <a href="tel:+306985064875">+30 698 506 4875</a>
                </p>
            </div>
        </form>
    </div>
</div>
</div><!-- /container -->
