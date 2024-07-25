<?php
// Check if ACF is active
if (function_exists('acf_add_local_field_group')) {
    // Add ACF fields for Business Listings
    acf_add_local_field_group(array(
        'key' => 'group_1',
        'title' => 'Business Listing Details',
        'fields' => array(
            array(
                'key' => 'field_1',
                'label' => 'Business Name',
                'name' => 'business_name',
                'type' => 'text',
            ),
            array(
                'key' => 'field_2',
                'label' => 'Type of Company',
                'name' => 'company_type',
                'type' => 'select',
                'choices' => array(
                    'Pvt Ltd' => 'Pvt Ltd',
                    'LLP' => 'LLP',
                    'OPC' => 'OPC',
                ),
            ),
            array(
                'key' => 'field_3',
                'label' => 'GST No.',
                'name' => 'gst_no',
                'type' => 'text',
            ),
            array(
                'key' => 'field_4',
                'label' => 'TAN/PAN',
                'name' => 'tan_pan',
                'type' => 'text',
            ),
            array(
                'key' => 'field_5',
                'label' => 'Location/s',
                'name' => 'location',
                'type' => 'text',
            ),
            array(
                'key' => 'field_6',
                'label' => 'Website',
                'name' => 'website',
                'type' => 'url',
            ),
            array(
                'key' => 'field_7',
                'label' => 'Social Media',
                'name' => 'social_media',
                'type' => 'text',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'business_listing',
                ),
            ),
        ),
    ));

    // Add ACF fields for Services and Products
    acf_add_local_field_group(array(
        'key' => 'group_2',
        'title' => 'Service/Product Details',
        'fields' => array(
            array(
                'key' => 'field_8',
                'label' => 'Description',
                'name' => 'description',
                'type' => 'textarea',
            ),
            array(
                'key' => 'field_9',
                'label' => 'Price',
                'name' => 'price',
                'type' => 'number',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'service_product',
                ),
            ),
        ),
    ));

    // Add ACF fields for Leads
    acf_add_local_field_group(array(
        'key' => 'group_3',
        'title' => 'Lead Details',
        'fields' => array(
            array(
                'key' => 'field_10',
                'label' => 'Lead Details',
                'name' => 'lead_details',
                'type' => 'textarea',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'lead',
                ),
            ),
        ),
    ));
}
?>
