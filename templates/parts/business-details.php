<?php
/**
 * Template part for displaying business details
 *
 * @package Happy_Business_Listing
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$post_id = get_the_ID();
?>
<div class="business-details-section">
    <?php echo hbl_get_business_details($post_id); ?>
</div>