<?php
/**
 * YOLO Contact Form Template
 *
 * Shortcode: [yolo_contact_form]
 * Matches Contact Form 7 styling
 *
 * @package YOLO_Yacht_Search
 * @subpackage Public/Partials
 * @since 17.5
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get form ID for unique identification
$form_id = 'yolo-contact-form-' . uniqid();
?>

<div class="yolo-contact-form-wrapper wpcf7">
    <form id="<?php echo esc_attr($form_id); ?>" class="yolo-contact-form wpcf7-form" method="post">
        <?php wp_nonce_field('yolo_contact_form', 'yolo_contact_nonce'); ?>
        
        <!-- Name Field -->
        <p>
            <label for="<?php echo esc_attr($form_id); ?>-name">
                Your Name <span class="required">*</span>
            </label>
            <span class="wpcf7-form-control-wrap">
                <input type="text" 
                       id="<?php echo esc_attr($form_id); ?>-name"
                       name="contact_name" 
                       class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" 
                       placeholder="Enter your full name"
                       required>
            </span>
        </p>
        
        <!-- Email Field -->
        <p>
            <label for="<?php echo esc_attr($form_id); ?>-email">
                Your Email <span class="required">*</span>
            </label>
            <span class="wpcf7-form-control-wrap">
                <input type="email" 
                       id="<?php echo esc_attr($form_id); ?>-email"
                       name="contact_email" 
                       class="wpcf7-form-control wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email" 
                       placeholder="your.email@example.com"
                       required>
            </span>
        </p>
        
        <!-- Phone Field -->
        <p>
            <label for="<?php echo esc_attr($form_id); ?>-phone">
                Phone Number
            </label>
            <span class="wpcf7-form-control-wrap">
                <input type="tel" 
                       id="<?php echo esc_attr($form_id); ?>-phone"
                       name="contact_phone" 
                       class="wpcf7-form-control wpcf7-text" 
                       placeholder="+30 123 456 7890">
            </span>
        </p>
        
        <!-- Subject Field -->
        <p>
            <label for="<?php echo esc_attr($form_id); ?>-subject">
                Subject <span class="required">*</span>
            </label>
            <span class="wpcf7-form-control-wrap">
                <input type="text" 
                       id="<?php echo esc_attr($form_id); ?>-subject"
                       name="contact_subject" 
                       class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" 
                       placeholder="What is this regarding?"
                       required>
            </span>
        </p>
        
        <!-- Message Field -->
        <p>
            <label for="<?php echo esc_attr($form_id); ?>-message">
                Your Message <span class="required">*</span>
            </label>
            <span class="wpcf7-form-control-wrap">
                <textarea id="<?php echo esc_attr($form_id); ?>-message"
                          name="contact_message" 
                          class="wpcf7-form-control wpcf7-textarea wpcf7-validates-as-required" 
                          placeholder="Please enter your message here..."
                          rows="6"
                          required></textarea>
            </span>
        </p>
        
        <!-- Submit Button -->
        <p>
            <button type="submit" class="wpcf7-form-control wpcf7-submit">
                <span class="submit-text">Send Message</span>
                <span class="submit-spinner" style="display: none;">Sending...</span>
            </button>
        </p>
        
        <!-- Response Message Area -->
        <div class="wpcf7-response-output" style="display: none;"></div>
    </form>
</div>

<style>
/* Additional styling specific to YOLO contact form */
.yolo-contact-form-wrapper {
    max-width: 700px;
    margin: 0 auto;
}

.yolo-contact-form .required {
    color: #C84B4B;
    font-weight: 600;
}

.yolo-contact-form .wpcf7-form-control-wrap {
    display: block;
    position: relative;
}

/* Submit button loading state */
.yolo-contact-form .wpcf7-submit.is-loading .submit-text {
    display: none;
}

.yolo-contact-form .wpcf7-submit.is-loading .submit-spinner {
    display: inline !important;
}

.yolo-contact-form .wpcf7-submit.is-loading {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Success animation */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.yolo-contact-form .wpcf7-response-output {
    animation: slideDown 0.3s ease;
}
</style>

<script>
var yolo_ajax = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>'
};

jQuery(document).ready(function($) {
    $('#<?php echo esc_js($form_id); ?>').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('.wpcf7-submit');
        var $responseOutput = $form.find('.wpcf7-response-output');
        
        // Disable submit button
        $submitBtn.addClass('is-loading').prop('disabled', true);
        
        // Hide previous messages
        $responseOutput.hide().removeClass('wpcf7-mail-sent-ok wpcf7-mail-sent-ng wpcf7-validation-errors');
        
        // Clear previous validation errors
        $form.find('.wpcf7-not-valid').removeClass('wpcf7-not-valid');
        $form.find('.wpcf7-not-valid-tip').remove();
        
        // Prepare form data
        var formData = {
            action: 'yolo_submit_contact_form',
            nonce: $form.find('#yolo_contact_nonce').val(),
            contact_name: $form.find('[name="contact_name"]').val(),
            contact_email: $form.find('[name="contact_email"]').val(),
            contact_phone: $form.find('[name="contact_phone"]').val(),
            contact_subject: $form.find('[name="contact_subject"]').val(),
            contact_message: $form.find('[name="contact_message"]').val()
        };
        
        // Submit via AJAX
        $.ajax({
            url: yolo_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $responseOutput
                        .addClass('wpcf7-mail-sent-ok')
                        .html(response.data.message)
                        .slideDown();
                    
                    // Reset form
                    $form[0].reset();
                    
                    // Hide success message after 5 seconds
                    setTimeout(function() {
                        $responseOutput.slideUp();
                    }, 5000);
                } else {
                    // Show error message
                    $responseOutput
                        .addClass('wpcf7-mail-sent-ng')
                        .html(response.data.message || 'An error occurred. Please try again.')
                        .slideDown();
                    
                    // Show field-specific errors if any
                    if (response.data.errors) {
                        $.each(response.data.errors, function(field, message) {
                            var $field = $form.find('[name="' + field + '"]');
                            $field.addClass('wpcf7-not-valid');
                            $field.after('<span class="wpcf7-not-valid-tip">' + message + '</span>');
                        });
                    }
                }
            },
            error: function() {
                $responseOutput
                    .addClass('wpcf7-mail-sent-ng')
                    .html('A network error occurred. Please check your connection and try again.')
                    .slideDown();
            },
            complete: function() {
                // Re-enable submit button
                $submitBtn.removeClass('is-loading').prop('disabled', false);
            }
        });
    });
});
</script>
