<?php
/**
 * REST API for Happy Business Listing
 *
 * Provides REST API endpoints for external integrations and mobile apps.
 *
 * @package Happy_Business_Listing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register REST API routes on init
add_action('rest_api_init', 'hbl_register_rest_routes');

/**
 * Register REST API routes
 */
function hbl_register_rest_routes() {
    // Business Listings endpoints
    register_rest_route('hbl/v1', '/businesses', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'hbl_get_businesses_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'per_page' => array(
                    'default' => 10,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($param) {
                        return $param <= 100;
                    }
                ),
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                ),
                'search' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'location' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'company_type' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'orderby' => array(
                    'default' => 'date',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'order' => array(
                    'default' => 'DESC',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ),
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'hbl_create_business_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'business_name' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function($param) {
                        return !empty($param);
                    }
                ),
                'company_type' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'gst_no' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'location' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'website' => array(
                    'sanitize_callback' => 'esc_url_raw'
                ),
                'whatsapp_number' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'email' => array(
                    'sanitize_callback' => 'sanitize_email'
                ),
                'contact_name' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'phone' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        )
    ));
    
    // Single business endpoint
    register_rest_route('hbl/v1', '/businesses/(?P<id>\d+)', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'hbl_get_business_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ),
        array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'hbl_update_business_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        ),
        array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'hbl_delete_business_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                )
            )
        )
    ));
    
    // Services and Products endpoints
    register_rest_route('hbl/v1', '/services', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'hbl_get_services_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'business_id' => array(
                    'sanitize_callback' => 'absint'
                ),
                'per_page' => array(
                    'default' => 10,
                    'sanitize_callback' => 'absint'
                ),
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                )
            )
        ),
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'hbl_create_service_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'title' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'content' => array(
                    'sanitize_callback' => 'wp_kses_post'
                ),
                'business_id' => array(
                    'required' => true,
                    'sanitize_callback' => 'absint'
                )
            )
        )
    ));
    
    // Leads endpoints
    register_rest_route('hbl/v1', '/leads', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'hbl_get_leads_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'business_id' => array(
                    'sanitize_callback' => 'absint'
                ),
                'per_page' => array(
                    'default' => 10,
                    'sanitize_callback' => 'absint'
                ),
                'page' => array(
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                )
            )
        ),
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'hbl_create_lead_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'business_id' => array(
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ),
                'name' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'email' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_email'
                ),
                'phone' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'message' => array(
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'service_interest' => array(
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        )
    ));
    
    // Search endpoint
    register_rest_route('hbl/v1', '/search', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'hbl_search_businesses_rest',
        'permission_callback' => 'hbl_rest_permission_check',
        'args' => array(
            'q' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'location' => array(
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'company_type' => array(
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'per_page' => array(
                'default' => 10,
                'sanitize_callback' => 'absint'
            )
        )
    ));
    
    // WhatsApp integration endpoints
    register_rest_route('hbl/v1', '/whatsapp/send', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'hbl_send_whatsapp_rest',
        'permission_callback' => 'hbl_rest_permission_check',
        'args' => array(
            'to' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'message' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_textarea_field'
            ),
            'business_id' => array(
                'sanitize_callback' => 'absint'
            )
        )
    ));
    
    // Statistics endpoint
    register_rest_route('hbl/v1', '/stats', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'hbl_get_stats_rest',
        'permission_callback' => 'hbl_rest_permission_check'
    ));
    
    // User management endpoints
    register_rest_route('hbl/v1', '/users/business', array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'hbl_create_business_user_rest',
            'permission_callback' => 'hbl_rest_permission_check',
            'args' => array(
                'username' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_user'
                ),
                'email' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_email'
                ),
                'password' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'business_id' => array(
                    'required' => true,
                    'sanitize_callback' => 'absint'
                )
            )
        )
    ));
}

/**
 * Check REST API permissions
 *
 * @param WP_REST_Request $request Request object
 * @return bool|WP_Error
 */
function hbl_rest_permission_check($request) {
    // Allow public access for read operations
    if (in_array($request->get_method(), array('GET', 'HEAD'))) {
        return true;
    }
    
    // Check if user is authenticated for write operations
    if (!is_user_logged_in()) {
        return new WP_Error(
            'rest_forbidden',
            __('You must be logged in to perform this action.', 'happy-business-listing'),
            array('status' => 401)
        );
    }
    
    // Check if user has appropriate capabilities
    if (!current_user_can('edit_posts')) {
        return new WP_Error(
            'rest_forbidden',
            __('You do not have permission to perform this action.', 'happy-business-listing'),
            array('status' => 403)
        );
    }
    
    return true;
}

/**
 * Get businesses via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_get_businesses_rest($request) {
    $per_page = $request->get_param('per_page');
    $page = $request->get_param('page');
    $search = $request->get_param('search');
    $location = $request->get_param('location');
    $company_type = $request->get_param('company_type');
    $orderby = $request->get_param('orderby');
    $order = $request->get_param('order');
    
    $args = array(
        'post_type' => 'business_listing',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'orderby' => $orderby,
        'order' => $order
    );
    
    // Add search parameter
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // Add meta query for filters
    $meta_query = array();
    
    if (!empty($location)) {
        $meta_query[] = array(
            'key' => 'location',
            'value' => $location,
            'compare' => 'LIKE'
        );
    }
    
    if (!empty($company_type)) {
        $meta_query[] = array(
            'key' => 'company_type',
            'value' => $company_type,
            'compare' => '='
        );
    }
    
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }
    
    $query = new WP_Query($args);
    $businesses = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $businesses[] = hbl_format_business_for_api(get_post());
        }
    }
    
    wp_reset_postdata();
    
    return new WP_REST_Response(array(
        'businesses' => $businesses,
        'total' => $query->found_posts,
        'total_pages' => $query->max_num_pages,
        'current_page' => $page
    ), 200);
}

/**
 * Get single business via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_get_business_rest($request) {
    $business_id = $request->get_param('id');
    $business = get_post($business_id);
    
    if (!$business || $business->post_type !== 'business_listing') {
        return new WP_Error(
            'business_not_found',
            __('Business not found.', 'happy-business-listing'),
            array('status' => 404)
        );
    }
    
    return new WP_REST_Response(hbl_format_business_for_api($business), 200);
}

/**
 * Create business via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_create_business_rest($request) {
    $business_data = array(
        'post_title' => $request->get_param('business_name'),
        'post_content' => $request->get_param('content', ''),
        'post_status' => 'publish',
        'post_type' => 'business_listing'
    );
    
    $business_id = wp_insert_post($business_data);
    
    if (is_wp_error($business_id)) {
        return $business_id;
    }
    
    // Add business meta data
    $meta_fields = array(
        'business_name', 'company_type', 'gst_no', 'location', 'website',
        'whatsapp_number', 'email', 'contact_name', 'phone'
    );
    
    foreach ($meta_fields as $field) {
        $value = $request->get_param($field);
        if (!empty($value)) {
            update_post_meta($business_id, $field, $value);
        }
    }
    
    // Log the creation
    if (hbl_security_logging_enabled()) {
        hbl_log_security_event(
            'Business created via REST API',
            'info',
            array(
                'business_id' => $business_id,
                'user_id' => get_current_user_id()
            )
        );
    }
    
    $business = get_post($business_id);
    
    return new WP_REST_Response(hbl_format_business_for_api($business), 201);
}

/**
 * Update business via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_update_business_rest($request) {
    $business_id = $request->get_param('id');
    $business = get_post($business_id);
    
    if (!$business || $business->post_type !== 'business_listing') {
        return new WP_Error(
            'business_not_found',
            __('Business not found.', 'happy-business-listing'),
            array('status' => 404)
        );
    }
    
    // Check if user can edit this business
    if (!current_user_can('edit_post', $business_id)) {
        return new WP_Error(
            'rest_forbidden',
            __('You do not have permission to edit this business.', 'happy-business-listing'),
            array('status' => 403)
        );
    }
    
    $update_data = array('ID' => $business_id);
    
    if ($request->get_param('business_name')) {
        $update_data['post_title'] = $request->get_param('business_name');
    }
    
    if ($request->get_param('content')) {
        $update_data['post_content'] = $request->get_param('content');
    }
    
    $updated_id = wp_update_post($update_data);
    
    if (is_wp_error($updated_id)) {
        return $updated_id;
    }
    
    // Update meta fields
    $meta_fields = array(
        'business_name', 'company_type', 'gst_no', 'location', 'website',
        'whatsapp_number', 'email', 'contact_name', 'phone'
    );
    
    foreach ($meta_fields as $field) {
        $value = $request->get_param($field);
        if ($value !== null) {
            update_post_meta($business_id, $field, $value);
        }
    }
    
    $updated_business = get_post($business_id);
    
    return new WP_REST_Response(hbl_format_business_for_api($updated_business), 200);
}

/**
 * Delete business via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_delete_business_rest($request) {
    $business_id = $request->get_param('id');
    $business = get_post($business_id);
    
    if (!$business || $business->post_type !== 'business_listing') {
        return new WP_Error(
            'business_not_found',
            __('Business not found.', 'happy-business-listing'),
            array('status' => 404)
        );
    }
    
    // Check if user can delete this business
    if (!current_user_can('delete_post', $business_id)) {
        return new WP_Error(
            'rest_forbidden',
            __('You do not have permission to delete this business.', 'happy-business-listing'),
            array('status' => 403)
        );
    }
    
    $deleted = wp_delete_post($business_id, true);
    
    if (!$deleted) {
        return new WP_Error(
            'delete_failed',
            __('Failed to delete business.', 'happy-business-listing'),
            array('status' => 500)
        );
    }
    
    return new WP_REST_Response(array(
        'message' => __('Business deleted successfully.', 'happy-business-listing')
    ), 200);
}

/**
 * Get services via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_get_services_rest($request) {
    $business_id = $request->get_param('business_id');
    $per_page = $request->get_param('per_page');
    $page = $request->get_param('page');
    
    $args = array(
        'post_type' => 'service_product',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page
    );
    
    if ($business_id) {
        $args['meta_query'] = array(
            array(
                'key' => 'business_id',
                'value' => $business_id,
                'compare' => '='
            )
        );
    }
    
    $query = new WP_Query($args);
    $services = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $services[] = hbl_format_service_for_api(get_post());
        }
    }
    
    wp_reset_postdata();
    
    return new WP_REST_Response(array(
        'services' => $services,
        'total' => $query->found_posts,
        'total_pages' => $query->max_num_pages,
        'current_page' => $page
    ), 200);
}

/**
 * Create service via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_create_service_rest($request) {
    $service_data = array(
        'post_title' => $request->get_param('title'),
        'post_content' => $request->get_param('content', ''),
        'post_status' => 'publish',
        'post_type' => 'service_product'
    );
    
    $service_id = wp_insert_post($service_data);
    
    if (is_wp_error($service_id)) {
        return $service_id;
    }
    
    // Add business association
    $business_id = $request->get_param('business_id');
    if ($business_id) {
        update_post_meta($service_id, 'business_id', $business_id);
    }
    
    $service = get_post($service_id);
    
    return new WP_REST_Response(hbl_format_service_for_api($service), 201);
}

/**
 * Get leads via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_get_leads_rest($request) {
    $business_id = $request->get_param('business_id');
    $per_page = $request->get_param('per_page');
    $page = $request->get_param('page');
    
    $args = array(
        'post_type' => 'lead',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page
    );
    
    if ($business_id) {
        $args['meta_query'] = array(
            array(
                'key' => 'business_id',
                'value' => $business_id,
                'compare' => '='
            )
        );
    }
    
    $query = new WP_Query($args);
    $leads = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $leads[] = hbl_format_lead_for_api(get_post());
        }
    }
    
    wp_reset_postdata();
    
    return new WP_REST_Response(array(
        'leads' => $leads,
        'total' => $query->found_posts,
        'total_pages' => $query->max_num_pages,
        'current_page' => $page
    ), 200);
}

/**
 * Create lead via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_create_lead_rest($request) {
    $lead_data = array(
        'post_title' => $request->get_param('name'),
        'post_content' => $request->get_param('message', ''),
        'post_status' => 'publish',
        'post_type' => 'lead'
    );
    
    $lead_id = wp_insert_post($lead_data);
    
    if (is_wp_error($lead_id)) {
        return $lead_id;
    }
    
    // Add lead meta data
    $meta_fields = array(
        'business_id', 'name', 'email', 'phone', 'message', 'service_interest'
    );
    
    foreach ($meta_fields as $field) {
        $value = $request->get_param($field);
        if (!empty($value)) {
            update_post_meta($lead_id, $field, $value);
        }
    }
    
    // Send WhatsApp notification if enabled
    $business_id = $request->get_param('business_id');
    if ($business_id && get_option('hbl_whatsapp_integration')) {
        $phone = $request->get_param('phone');
        if ($phone) {
            $message = sprintf(
                __('New lead received from %s (%s): %s', 'happy-business-listing'),
                $request->get_param('name'),
                $request->get_param('email'),
                $request->get_param('message')
            );
            
            hbl_send_whatsapp_message($message, $phone);
        }
    }
    
    $lead = get_post($lead_id);
    
    return new WP_REST_Response(hbl_format_lead_for_api($lead), 201);
}

/**
 * Search businesses via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_search_businesses_rest($request) {
    $search_query = $request->get_param('q');
    $location = $request->get_param('location');
    $company_type = $request->get_param('company_type');
    $per_page = $request->get_param('per_page');
    
    $args = array(
        'post_type' => 'business_listing',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        's' => $search_query
    );
    
    // Add meta query for filters
    $meta_query = array();
    
    if (!empty($location)) {
        $meta_query[] = array(
            'key' => 'location',
            'value' => $location,
            'compare' => 'LIKE'
        );
    }
    
    if (!empty($company_type)) {
        $meta_query[] = array(
            'key' => 'company_type',
            'value' => $company_type,
            'compare' => '='
        );
    }
    
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }
    
    $query = new WP_Query($args);
    $businesses = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $businesses[] = hbl_format_business_for_api(get_post());
        }
    }
    
    wp_reset_postdata();
    
    return new WP_REST_Response(array(
        'businesses' => $businesses,
        'total' => $query->found_posts,
        'search_query' => $search_query
    ), 200);
}

/**
 * Send WhatsApp message via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_send_whatsapp_rest($request) {
    $to = $request->get_param('to');
    $message = $request->get_param('message');
    $business_id = $request->get_param('business_id');
    
    $result = hbl_send_whatsapp_message($message, $to);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    return new WP_REST_Response(array(
        'message' => __('WhatsApp message sent successfully.', 'happy-business-listing'),
        'to' => $to
    ), 200);
}

/**
 * Get statistics via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_get_stats_rest($request) {
    $business_count = wp_count_posts('business_listing');
    $service_count = wp_count_posts('service_product');
    $lead_count = wp_count_posts('lead');
    
    $stats = array(
        'businesses' => array(
            'total' => $business_count->publish,
            'draft' => $business_count->draft,
            'pending' => $business_count->pending
        ),
        'services' => array(
            'total' => $service_count->publish,
            'draft' => $service_count->draft
        ),
        'leads' => array(
            'total' => $lead_count->publish,
            'new' => $lead_count->publish // Assuming all leads are new
        ),
        'users' => array(
            'business_users' => count_users()['avail_roles']['business_user'] ?? 0
        )
    );
    
    return new WP_REST_Response($stats, 200);
}

/**
 * Create business user via REST API
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error
 */
function hbl_create_business_user_rest($request) {
    $username = $request->get_param('username');
    $email = $request->get_param('email');
    $password = $request->get_param('password');
    $business_id = $request->get_param('business_id');
    
    // Check if username exists
    if (username_exists($username)) {
        return new WP_Error(
            'username_exists',
            __('Username already exists.', 'happy-business-listing'),
            array('status' => 400)
        );
    }
    
    // Check if email exists
    if (email_exists($email)) {
        return new WP_Error(
            'email_exists',
            __('Email already exists.', 'happy-business-listing'),
            array('status' => 400)
        );
    }
    
    // Create user
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        return $user_id;
    }
    
    // Set user role
    $user = new WP_User($user_id);
    $user->set_role('business_user');
    
    // Associate with business
    if ($business_id) {
        update_user_meta($user_id, 'user_business_id', $business_id);
        update_post_meta($business_id, 'user_id', $user_id);
    }
    
    return new WP_REST_Response(array(
        'user_id' => $user_id,
        'username' => $username,
        'email' => $email,
        'message' => __('Business user created successfully.', 'happy-business-listing')
    ), 201);
}

/**
 * Format business for API response
 *
 * @param WP_Post $business Business post object
 * @return array Formatted business data
 */
function hbl_format_business_for_api($business) {
    $meta = get_post_meta($business->ID);
    
    return array(
        'id' => $business->ID,
        'title' => $business->post_title,
        'content' => $business->post_content,
        'excerpt' => $business->post_excerpt,
        'date' => $business->post_date,
        'modified' => $business->post_modified,
        'status' => $business->post_status,
        'slug' => $business->post_name,
        'link' => get_permalink($business->ID),
        'featured_image' => get_the_post_thumbnail_url($business->ID, 'full'),
        'meta' => array(
            'business_name' => $meta['business_name'][0] ?? '',
            'company_type' => $meta['company_type'][0] ?? '',
            'gst_no' => $meta['gst_no'][0] ?? '',
            'tan_pan' => $meta['tan_pan'][0] ?? '',
            'location' => $meta['location'][0] ?? '',
            'website' => $meta['website'][0] ?? '',
            'social_media_handles' => $meta['social_media_handles'][0] ?? '',
            'whatsapp_number' => $meta['whatsapp_number'][0] ?? '',
            'email' => $meta['email'][0] ?? '',
            'contact_name' => $meta['contact_name'][0] ?? '',
            'phone' => $meta['phone'][0] ?? '',
            'user_id' => $meta['user_id'][0] ?? ''
        )
    );
}

/**
 * Format service for API response
 *
 * @param WP_Post $service Service post object
 * @return array Formatted service data
 */
function hbl_format_service_for_api($service) {
    $meta = get_post_meta($service->ID);
    
    return array(
        'id' => $service->ID,
        'title' => $service->post_title,
        'content' => $service->post_content,
        'excerpt' => $service->post_excerpt,
        'date' => $service->post_date,
        'modified' => $service->post_modified,
        'status' => $service->post_status,
        'slug' => $service->post_name,
        'link' => get_permalink($service->ID),
        'meta' => array(
            'business_id' => $meta['business_id'][0] ?? ''
        )
    );
}

/**
 * Format lead for API response
 *
 * @param WP_Post $lead Lead post object
 * @return array Formatted lead data
 */
function hbl_format_lead_for_api($lead) {
    $meta = get_post_meta($lead->ID);
    
    return array(
        'id' => $lead->ID,
        'title' => $lead->post_title,
        'content' => $lead->post_content,
        'date' => $lead->post_date,
        'modified' => $lead->post_modified,
        'status' => $lead->post_status,
        'meta' => array(
            'business_id' => $meta['business_id'][0] ?? '',
            'name' => $meta['name'][0] ?? '',
            'email' => $meta['email'][0] ?? '',
            'phone' => $meta['phone'][0] ?? '',
            'message' => $meta['message'][0] ?? '',
            'service_interest' => $meta['service_interest'][0] ?? ''
        )
    );
}

/**
 * Add REST API documentation
 */
function hbl_add_rest_api_documentation() {
    ?>
    <div class="wrap">
        <h1><?php _e('REST API Documentation', 'happy-business-listing'); ?></h1>
        
        <h2><?php _e('Base URL', 'happy-business-listing'); ?></h2>
        <p><code><?php echo rest_url('hbl/v1/'); ?></code></p>
        
        <h2><?php _e('Authentication', 'happy-business-listing'); ?></h2>
        <p><?php _e('For write operations, you need to be authenticated. Use WordPress authentication methods like cookies or application passwords.', 'happy-business-listing'); ?></p>
        
        <h2><?php _e('Endpoints', 'happy-business-listing'); ?></h2>
        
        <h3><?php _e('Businesses', 'happy-business-listing'); ?></h3>
        <ul>
            <li><strong>GET</strong> <code>/businesses</code> - <?php _e('List businesses', 'happy-business-listing'); ?></li>
            <li><strong>POST</strong> <code>/businesses</code> - <?php _e('Create business', 'happy-business-listing'); ?></li>
            <li><strong>GET</strong> <code>/businesses/{id}</code> - <?php _e('Get business', 'happy-business-listing'); ?></li>
            <li><strong>PUT</strong> <code>/businesses/{id}</code> - <?php _e('Update business', 'happy-business-listing'); ?></li>
            <li><strong>DELETE</strong> <code>/businesses/{id}</code> - <?php _e('Delete business', 'happy-business-listing'); ?></li>
        </ul>
        
        <h3><?php _e('Services', 'happy-business-listing'); ?></h3>
        <ul>
            <li><strong>GET</strong> <code>/services</code> - <?php _e('List services', 'happy-business-listing'); ?></li>
            <li><strong>POST</strong> <code>/services</code> - <?php _e('Create service', 'happy-business-listing'); ?></li>
        </ul>
        
        <h3><?php _e('Leads', 'happy-business-listing'); ?></h3>
        <ul>
            <li><strong>GET</strong> <code>/leads</code> - <?php _e('List leads', 'happy-business-listing'); ?></li>
            <li><strong>POST</strong> <code>/leads</code> - <?php _e('Create lead', 'happy-business-listing'); ?></li>
        </ul>
        
        <h3><?php _e('Search', 'happy-business-listing'); ?></h3>
        <ul>
            <li><strong>GET</strong> <code>/search?q={query}</code> - <?php _e('Search businesses', 'happy-business-listing'); ?></li>
        </ul>
        
        <h3><?php _e('WhatsApp', 'happy-business-listing'); ?></h3>
        <ul>
            <li><strong>POST</strong> <code>/whatsapp/send</code> - <?php _e('Send WhatsApp message', 'happy-business-listing'); ?></li>
        </ul>
        
        <h3><?php _e('Statistics', 'happy-business-listing'); ?></h3>
        <ul>
            <li><strong>GET</strong> <code>/stats</code> - <?php _e('Get plugin statistics', 'happy-business-listing'); ?></li>
        </ul>
        
        <h3><?php _e('Users', 'happy-business-listing'); ?></h3>
        <ul>
            <li><strong>POST</strong> <code>/users/business</code> - <?php _e('Create business user', 'happy-business-listing'); ?></li>
        </ul>
    </div>
    <?php
} 