<?php
/**
 * Template part for displaying business enquiry form
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get post ID and args
$post_id = isset($args['post_id']) ? $args['post_id'] : get_the_ID();
$title = isset($args['title']) ? $args['title'] : __('Contact Business', 'happy-business-listing');
$button_text = isset($args['button_text']) ? $args['button_text'] : __('Send Enquiry', 'happy-business-listing');
$show_product_selection = isset($args['show_product_selection']) ? $args['show_product_selection'] : false;
$show_quantity = isset($args['show_quantity']) ? $args['show_quantity'] : false;
$form_class = isset($args['form_class']) ? $args['form_class'] : '';
$product_id = isset($args['product_id']) ? $args['product_id'] : 0;

// Get business data
$business_name = get_the_title($post_id);

// Get services/products for selection
$products = array();
if ($show_product_selection) {
    $products_query = new WP_Query(array(
        'post_type' => 'service_product',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'business_id',
                'value' => $post_id,
                'compare' => '='
            )
        )
    ));
    
    if ($products_query->have_posts()) {
        while ($products_query->have_posts()) {
            $products_query->the_post();
            $products[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'selected' => get_the_ID() == $product_id
            );
        }
        wp_reset_postdata();
    }
}

// Generate unique form ID
$form_id = 'hbl-enquiry-form-' . uniqid();
?>

<div class="hbl-enquiry-form-container <?php echo esc_attr($form_class); ?>">
    <h3 class="hbl-enquiry-form-title"><?php echo esc_html($title); ?></h3>
    
    <form id="<?php echo esc_attr($form_id); ?>" class="hbl-enquiry-form" method="post">
        <?php wp_nonce_field('hbl_enquiry_nonce', 'hbl_enquiry_nonce'); ?>
        <input type="hidden" name="action" value="hbl_submit_enquiry">
        <input type="hidden" name="business_id" value="<?php echo esc_attr($post_id); ?>">
        <?php if ($product_id) : ?>
            <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
        <?php endif; ?>
        
        <div class="hbl-form-row">
            <label for="<?php echo esc_attr($form_id); ?>-name" class="hbl-form-label"><?php _e('Your Name', 'happy-business-listing'); ?> <span class="required">*</span></label>
            <input type="text" name="name" id="<?php echo esc_attr($form_id); ?>-name" class="hbl-form-input" required>
        </div>
        
        <div class="hbl-form-row">
            <label for="<?php echo esc_attr($form_id); ?>-email" class="hbl-form-label"><?php _e('Email Address', 'happy-business-listing'); ?> <span class="required">*</span></label>
            <input type="email" name="email" id="<?php echo esc_attr($form_id); ?>-email" class="hbl-form-input" required>
        </div>
        
        <div class="hbl-form-row">
            <label for="<?php echo esc_attr($form_id); ?>-phone" class="hbl-form-label"><?php _e('Phone Number', 'happy-business-listing'); ?></label>
            <input type="tel" name="phone" id="<?php echo esc_attr($form_id); ?>-phone" class="hbl-form-input">
        </div>
        
        <?php if ($show_product_selection && !empty($products)) : ?>
            <div class="hbl-form-row">
                <label for="<?php echo esc_attr($form_id); ?>-product" class="hbl-form-label"><?php _e('Product/Service', 'happy-business-listing'); ?></label>
                <select name="product_id" id="<?php echo esc_attr($form_id); ?>-product" class="hbl-form-select">
                    <option value=""><?php _e('Select a product/service', 'happy-business-listing'); ?></option>
                    <?php foreach ($products as $product) : ?>
                        <option value="<?php echo esc_attr($product['id']); ?>" <?php selected($product['selected'], true); ?>>
                            <?php echo esc_html($product['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        
        <?php if ($show_quantity) : ?>
            <div class="hbl-form-row">
                <label for="<?php echo esc_attr($form_id); ?>-quantity" class="hbl-form-label"><?php _e('Quantity', 'happy-business-listing'); ?></label>
                <input type="number" name="quantity" id="<?php echo esc_attr($form_id); ?>-quantity" class="hbl-form-input" min="1" value="1">
            </div>
        <?php endif; ?>
        
        <div class="hbl-form-row">
            <label for="<?php echo esc_attr($form_id); ?>-message" class="hbl-form-label"><?php _e('Message', 'happy-business-listing'); ?> <span class="required">*</span></label>
            <textarea name="message" id="<?php echo esc_attr($form_id); ?>-message" class="hbl-form-textarea" rows="5" required></textarea>
        </div>
        
        <!-- Anti-spam honeypot field -->
        <div class="hbl-form-honeypot">
            <label for="<?php echo esc_attr($form_id); ?>-website"><?php _e('Website', 'happy-business-listing'); ?></label>
            <input type="text" name="website" id="<?php echo esc_attr($form_id); ?>-website" tabindex="-1" autocomplete="off">
        </div>
        
        <div class="hbl-form-row hbl-form-submit">
            <button type="submit" class="hbl-form-button">
                <span class="hbl-button-text"><?php echo esc_html($button_text); ?></span>
                <span class="hbl-button-loading"><?php _e('Sending...', 'happy-business-listing'); ?></span>
            </button>
        </div>
        
        <div class="hbl-form-response"></div>
    </form>
</div>

<style>
    .hbl-enquiry-form-container {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .hbl-enquiry-form-title {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.4em;
        color: #333;
    }
    
    .hbl-form-row {
        margin-bottom: 15px;
    }
    
    .hbl-form-label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555;
    }
    
    .hbl-form-input,
    .hbl-form-select,
    .hbl-form-textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    
    .hbl-form-textarea {
        resize: vertical;
    }
    
    .hbl-form-honeypot {
        position: absolute;
        left: -9999px;
    }
    
    .hbl-form-submit {
        margin-top: 20px;
    }
    
    .hbl-form-button {
        background-color: #0066cc;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .hbl-form-button:hover {
        background-color: #0052a3;
    }
    
    .hbl-form-button.hbl-loading .hbl-button-text {
        display: none;
    }
    
    .hbl-form-button .hbl-button-loading {
        display: none;
    }
    
    .hbl-form-button.hbl-loading .hbl-button-loading {
        display: inline;
    }
    
    .hbl-form-response {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
        display: none;
    }
    
    .hbl-form-response.hbl-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        display: block;
    }
    
    .hbl-form-response.hbl-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        display: block;
    }
    
    .required {
        color: #dc3545;
    }
</style>

<script>
jQuery(document).ready(function($) {
    $('#<?php echo esc_js($form_id); ?>').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var response = form.find('.hbl-form-response');
        var submitButton = form.find('.hbl-form-button');
        
        // Anti-spam check
        if (form.find('input[name="website"]').val() !== '') {
            return false;
        }
        
        // Show loading state
        submitButton.addClass('hbl-loading');
        response.removeClass('hbl-success hbl-error').hide();
        
        // Send AJAX request
        $.ajax({
            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
            type: 'POST',
            data: form.serialize(),
            success: function(data) {
                submitButton.removeClass('hbl-loading');
                
                if (data.success) {
                    response.addClass('hbl-success').html(data.data.message).show();
                    form.find('input[type="text"], input[type="email"], input[type="tel"], textarea').val('');
                } else {
                    response.addClass('hbl-error').html(data.data.message).show();
                }
            },
            error: function() {
                submitButton.removeClass('hbl-loading');
                response.addClass('hbl-error').html('<?php _e('An error occurred. Please try again later.', 'happy-business-listing'); ?>').show();
            }
        });
    });
});
</script> 