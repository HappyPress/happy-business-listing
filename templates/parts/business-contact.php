<?php
/**
 * Template part for displaying business contact information
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
?>
<div class="business-contact-section">
    <h3><?php _e('Contact Information', 'happy-business-listing'); ?></h3>
    <?php echo hbl_get_contact_info($post_id); ?>
    <?php echo hbl_get_social_media_links($post_id, array('show_labels' => true)); ?>
</div>