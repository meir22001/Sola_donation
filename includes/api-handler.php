<?php
/**
 * Sola Payments API Handler
 * Processes payments through Sola Payments Gateway
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Sola Recurring API Configuration
define('SOLA_RECURRING_API_URL', 'https://api.cardknox.com/v2');
define('SOLA_RECURRING_API_VERSION', '2.1');

/**
 * Process payment through Sola Payments
 *
 * @param array $form_data Form data from frontend
 * @return array Result with success status and message
 */
function sola_donation_process_payment($form_data) {
    // Get settings
    $settings = get_option('sola_donation_settings');
    
    // Determine which API key to use
    $is_sandbox = isset($settings['sandbox_mode']) && $settings['sandbox_mode'];
    $api_key = $is_sandbox ? $settings['sandbox_key'] : $settings['production_key'];
    
    // Validate API key
    if (empty($api_key)) {
        return array(
            'success' => false,
            'message' => 'API key not configured. Please check plugin settings.'
        );
    }
    
    // Prepare API request
    $api_url = 'https://x1.cardknox.com/gateway';
    
    // Format expiry date (MMYY)
    $expiry = $form_data['expiry']; // Already formatted as MMYY from JS
    
    // Determine command based on donation type
    if ($form_data['donationType'] === 'monthly') {
        // For recurring payments, we'll use a different approach
        $result = sola_donation_setup_recurring($form_data, $api_key, $is_sandbox);
    } else {
        // One-time payment
        $result = sola_donation_process_one_time($form_data, $api_key, $is_sandbox);
    }
    
    return $result;
}

/**
 * Process one-time donation
 *
 * @param array $form_data Form data
 * @param string $api_key Sola API key
 * @param bool $is_sandbox Sandbox mode flag
 * @return array Result
 */
function sola_donation_process_one_time($form_data, $api_key, $is_sandbox) {
    $api_url = 'https://x1.cardknox.com/gateway';
    
    // Prepare request data
    $request_data = array(
        'xKey' => $api_key,
        'xVersion' => '5.0.0',
        'xSoftwareName' => 'Sola Donation Plugin',
        'xSoftwareVersion' => SOLA_DONATION_VERSION,
        'xCommand' => 'cc:sale',
        'xAmount' => number_format($form_data['amount'], 2, '.', ''),
        'xCardNum' => $form_data['cardNumber'],
        'xExp' => $form_data['expiry'],
        'xCVV' => $form_data['cvv'],
        'xBillFirstName' => $form_data['firstName'],
        'xBillLastName' => $form_data['lastName'],
        'xEmail' => $form_data['email'],
        'xBillPhone' => $form_data['phone'],
        'xBillStreet' => $form_data['address'],
        'xCurrency' => $form_data['currency'],
        'xInvoice' => 'DONATION-' . time() . '-' . rand(1000, 9999),
        'xDescription' => 'One-time donation',
        'xAllowDuplicate' => 'false'
    );
    
    // Log request (without sensitive data)
    sola_donation_log('One-time payment request', array(
        'amount' => $request_data['xAmount'],
        'currency' => $request_data['xCurrency'],
        'invoice' => $request_data['xInvoice'],
        'email' => $form_data['email'],
        'sandbox' => $is_sandbox
    ));
    
    // Make API request
    $response = wp_remote_post($api_url, array(
        'body' => $request_data,
        'timeout' => 30,
        'headers' => array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        )
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        sola_donation_log('API request failed', array('error' => $response->get_error_message()));
        return array(
            'success' => false,
            'message' => 'Payment gateway connection failed. Please try again.'
        );
    }
    
    // Parse response - API returns URL-encoded format, not JSON
    $body = wp_remote_retrieve_body($response);
    
    // Try to parse as URL-encoded first (default format from /gateway endpoint)
    parse_str($body, $result);
    
    // If that didn't work, try JSON (for /gatewayjson endpoint)
    if (empty($result) || !isset($result['xResult'])) {
        $json_result = json_decode($body, true);
        if ($json_result && isset($json_result['xResult'])) {
            $result = $json_result;
        }
    }
    
    // Log response
    sola_donation_log('API response received', array(
        'xResult' => isset($result['xResult']) ? $result['xResult'] : 'unknown',
        'xRefNum' => isset($result['xRefNum']) ? $result['xRefNum'] : 'none',
        'raw_body' => $body
    ));
    
    // Check result
    if (isset($result['xResult']) && $result['xResult'] === 'A') {
        // Approved
        return array(
            'success' => true,
            'message' => 'Payment successful! Transaction #' . $result['xRefNum'],
            'data' => array(
                'refNum' => $result['xRefNum'],
                'authAmount' => isset($result['xAuthAmount']) ? $result['xAuthAmount'] : $form_data['amount'],
                'authCode' => isset($result['xAuthCode']) ? $result['xAuthCode'] : '',
                'maskedCard' => isset($result['xMaskedCardNumber']) ? $result['xMaskedCardNumber'] : '',
                'cardType' => isset($result['xCardType']) ? $result['xCardType'] : '',
                'formData' => $form_data
            )
        );
    } else {
        // Declined or Error
        $error_message = isset($result['xError']) ? $result['xError'] : 'Payment was declined. Please check your card details and try again.';
        
        sola_donation_log('Payment declined', array(
            'xResult' => isset($result['xResult']) ? $result['xResult'] : 'unknown',
            'xError' => $error_message
        ));
        
        return array(
            'success' => false,
            'message' => $error_message
        );
    }
}

/**
 * Setup recurring donation using Sola Payments Recurring API
 *
 * This function integrates with the Sola Payments CreateSchedule API to create
 * automated recurring charges. The StartDate logic follows the documentation:
 * - If chargeNow is true: StartDate = today (immediate first charge)
 * - If chargeNow is false: StartDate = 1st of next month (first charge next month)
 *
 * @param array $form_data Form data from frontend
 * @param string $api_key Sola API key
 * @param bool $is_sandbox Sandbox mode flag
 * @return array Result with success status and message
 */
function sola_donation_setup_recurring($form_data, $api_key, $is_sandbox) {
    $gateway_url = 'https://x1.cardknox.com/gateway';
    
    // Step 1: Save the card as a token using the Gateway API
    $token_request = array(
        'xKey' => $api_key,
        'xVersion' => '5.0.0',
        'xSoftwareName' => 'Sola Donation Plugin',
        'xSoftwareVersion' => SOLA_DONATION_VERSION,
        'xCommand' => 'cc:save',
        'xCardNum' => $form_data['cardNumber'],
        'xExp' => $form_data['expiry'],
        'xCVV' => $form_data['cvv'],
        'xBillFirstName' => $form_data['firstName'],
        'xBillLastName' => $form_data['lastName'],
        'xEmail' => $form_data['email'],
        'xBillPhone' => $form_data['phone'],
        'xBillStreet' => $form_data['address']
    );
    
    sola_donation_log('Saving card token for recurring', array(
        'email' => $form_data['email'],
        'card_last4' => substr($form_data['cardNumber'], -4),
        'expiry' => $form_data['expiry']
    ));
    
    $token_response = wp_remote_post($gateway_url, array(
        'body' => $token_request,
        'timeout' => 30,
        'headers' => array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        )
    ));
    
    if (is_wp_error($token_response)) {
        return array(
            'success' => false,
            'message' => 'Failed to setup recurring donation. Please try again.'
        );
    }
    
    $token_body = wp_remote_retrieve_body($token_response);
    parse_str($token_body, $token_result);
    
    if (empty($token_result) || !isset($token_result['xResult'])) {
        $json_result = json_decode($token_body, true);
        if ($json_result) {
            $token_result = $json_result;
        }
    }
    
    sola_donation_log('Token API response', array(
        'xResult' => isset($token_result['xResult']) ? $token_result['xResult'] : 'unknown',
        'has_token' => !empty($token_result['xToken'])
    ));
    
    // Check if we got a valid token
    if (!isset($token_result['xToken']) || empty($token_result['xToken'])) {
        $error_message = 'Failed to save payment method.';
        if (isset($token_result['xError']) && !empty($token_result['xError'])) {
            $error_message = $token_result['xError'];
        }
        
        return array(
            'success' => false,
            'message' => $error_message
        );
    }
    
    $token = $token_result['xToken'];
    $charge_day = isset($form_data['chargeDay']) ? intval($form_data['chargeDay']) : 1;
    $charge_now = isset($form_data['chargeNow']) && $form_data['chargeNow'];
    
    // Step 2: Calculate StartDate based on chargeNow setting
    // Per Sola documentation:
    // - If StartDate is today or not specified, first charge happens immediately
    // - If StartDate is in the future, first charge happens on that date or the next matching schedule date
    if ($charge_now) {
        // Charge first donation from current month - use today's date
        $start_date = date('Y-m-d');
    } else {
        // Charge first donation from next month - use 1st of next month
        $start_date = date('Y-m-d', strtotime('first day of next month'));
    }
    
    sola_donation_log('Calculated StartDate', array(
        'chargeNow' => $charge_now,
        'chargeDay' => $charge_day,
        'startDate' => $start_date
    ));
    
    // Step 3: Create Schedule using Sola Recurring API
    // This creates the customer, payment method, and schedule in one call using NewCustomer/NewPaymentMethod
    $schedule_request = array(
        'SoftwareName' => 'Sola Donation Plugin',
        'SoftwareVersion' => SOLA_DONATION_VERSION,
        // Create new customer inline
        'NewCustomer' => array(
            'BillFirstName' => $form_data['firstName'],
            'BillLastName' => $form_data['lastName'],
            'Email' => $form_data['email'],
            'BillPhone' => $form_data['phone'],
            'BillStreet' => $form_data['address'],
            'CustomerNotes' => 'Created via Sola Donation Plugin'
        ),
        // Create new payment method inline
        'NewPaymentMethod' => array(
            'Token' => $token,
            'TokenType' => 'cc',
            'Exp' => $form_data['expiry'],
            'SetAsDefault' => true
        ),
        // Schedule settings
        'Amount' => number_format($form_data['amount'], 2, '.', ''),
        'Currency' => $form_data['currency'],
        'IntervalType' => 'month',
        'IntervalCount' => 1,
        'StartDate' => $start_date,
        'ScheduleName' => 'Donation - ' . $form_data['email'],
        'Description' => 'Monthly recurring donation',
        // Schedule Rule: Run on specific day of month
        'ScheduleRule' => array(
            'RuleType' => 'On',
            'DayOfMonth' => $charge_day
        ),
        // Additional settings
        'FailedTransactionRetryTimes' => 3,
        'DaysBetweenRetries' => 2,
        'AfterMaxRetriesAction' => 'ContinueNextInterval',
        'AllowInitialTransactionToDecline' => false,
        'CustReceipt' => true
    );
    
    // If charging now, pass CVV for the initial transaction
    if ($charge_now && !empty($form_data['cvv'])) {
        $schedule_request['Cvv'] = $form_data['cvv'];
    }
    
    sola_donation_log('Creating schedule in Sola Recurring API', array(
        'amount' => $schedule_request['Amount'],
        'currency' => $schedule_request['Currency'],
        'startDate' => $start_date,
        'chargeDay' => $charge_day,
        'email' => $form_data['email']
    ));
    
    // Make request to Sola Recurring API
    $schedule_response = sola_donation_make_recurring_api_request('/CreateSchedule', $schedule_request, $api_key);
    
    if (!$schedule_response['success']) {
        sola_donation_log('CreateSchedule failed', array(
            'error' => $schedule_response['message'],
            'response' => isset($schedule_response['response']) ? $schedule_response['response'] : null
        ));
        
        return array(
            'success' => false,
            'message' => $schedule_response['message']
        );
    }
    
    $schedule_id = isset($schedule_response['data']['ScheduleId']) ? $schedule_response['data']['ScheduleId'] : '';
    $customer_id = isset($schedule_response['data']['CustomerId']) ? $schedule_response['data']['CustomerId'] : '';
    $payment_method_id = isset($schedule_response['data']['PaymentMethodId']) ? $schedule_response['data']['PaymentMethodId'] : '';
    
    sola_donation_log('Schedule created successfully', array(
        'scheduleId' => $schedule_id,
        'customerId' => $customer_id,
        'paymentMethodId' => $payment_method_id
    ));
    
    // Step 4: Store recurring donation info in WordPress for admin reference
    $recurring_data = array(
        'token' => $token,
        'amount' => $form_data['amount'],
        'currency' => $form_data['currency'],
        'charge_day' => $charge_day,
        'donor_email' => $form_data['email'],
        'donor_name' => $form_data['firstName'] . ' ' . $form_data['lastName'],
        'created_at' => current_time('mysql'),
        'status' => 'active',
        // New fields from Sola Recurring API
        'sola_schedule_id' => $schedule_id,
        'sola_customer_id' => $customer_id,
        'sola_payment_method_id' => $payment_method_id,
        'start_date' => $start_date,
        'charge_now' => $charge_now
    );
    
    sola_donation_save_recurring_subscription($recurring_data);
    
    // Build success message based on charge timing
    if ($charge_now) {
        $success_message = sprintf(
            'Recurring donation setup successful! Your first payment has been processed. Future charges will occur on day %d of each month.',
            $charge_day
        );
    } else {
        $next_charge_date = date('F j, Y', strtotime($start_date));
        $success_message = sprintf(
            'Recurring donation setup successful! Your first charge will be on %s, then on day %d of each month.',
            $next_charge_date,
            $charge_day
        );
    }
    
    return array(
        'success' => true,
        'message' => $success_message,
        'data' => array(
            'recurring' => true,
            'scheduleId' => $schedule_id,
            'customerId' => $customer_id,
            'startDate' => $start_date,
            'chargeNow' => $charge_now,
            'formData' => $form_data
        )
    );
}

/**
 * Make request to Sola Recurring API
 *
 * @param string $endpoint API endpoint (e.g., '/CreateSchedule')
 * @param array $data Request data
 * @param string $api_key Sola API key
 * @return array Response with success status and data
 */
function sola_donation_make_recurring_api_request($endpoint, $data, $api_key) {
    $url = SOLA_RECURRING_API_URL . $endpoint;
    
    sola_donation_log('Making Recurring API request', array(
        'endpoint' => $endpoint,
        'url' => $url
    ));
    
    $response = wp_remote_post($url, array(
        'body' => json_encode($data),
        'timeout' => 45,
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => $api_key,
            'X-Recurring-Api-Version' => SOLA_RECURRING_API_VERSION
        )
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Failed to connect to payment gateway: ' . $response->get_error_message()
        );
    }
    
    $http_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    sola_donation_log('Recurring API response', array(
        'http_code' => $http_code,
        'result' => isset($result['Result']) ? $result['Result'] : 'unknown',
        'error' => isset($result['Error']) ? $result['Error'] : ''
    ));
    
    // Check for API-level errors
    if (!$result) {
        return array(
            'success' => false,
            'message' => 'Invalid response from payment gateway.',
            'response' => $body
        );
    }
    
    // Check Result field - 'S' = Success, 'E' = Error
    if (isset($result['Result']) && $result['Result'] === 'S') {
        return array(
            'success' => true,
            'data' => $result
        );
    }
    
    // Handle error response
    $error_message = isset($result['Error']) && !empty($result['Error']) 
        ? $result['Error'] 
        : 'Failed to create recurring schedule.';
    
    return array(
        'success' => false,
        'message' => $error_message,
        'response' => $result
    );
}

/**
 * Save recurring subscription data
 *
 * @param array $data Subscription data
 * @return int|WP_Error Post ID on success, WP_Error on failure
 */
function sola_donation_save_recurring_subscription($data) {
    // Build meta input array with all fields
    $meta_input = array(
        'sola_token' => $data['token'],
        'sola_amount' => $data['amount'],
        'sola_currency' => $data['currency'],
        'sola_charge_day' => $data['charge_day'],
        'sola_donor_email' => $data['donor_email'],
        'sola_donor_name' => $data['donor_name'],
        'sola_status' => $data['status']
    );
    
    // Add new Sola Recurring API fields if present
    if (isset($data['sola_schedule_id'])) {
        $meta_input['sola_schedule_id'] = $data['sola_schedule_id'];
    }
    if (isset($data['sola_customer_id'])) {
        $meta_input['sola_customer_id'] = $data['sola_customer_id'];
    }
    if (isset($data['sola_payment_method_id'])) {
        $meta_input['sola_payment_method_id'] = $data['sola_payment_method_id'];
    }
    if (isset($data['start_date'])) {
        $meta_input['sola_start_date'] = $data['start_date'];
    }
    if (isset($data['charge_now'])) {
        $meta_input['sola_charge_now'] = $data['charge_now'] ? 'yes' : 'no';
    }
    
    // Save as a custom post type
    $post_id = wp_insert_post(array(
        'post_type' => 'sola_recurring_donation',
        'post_title' => 'Recurring Donation - ' . $data['donor_name'],
        'post_status' => 'publish',
        'meta_input' => $meta_input
    ));
    
    return $post_id;
}

/**
 * Log donation activity
 *
 * @param string $message Log message
 * @param array $data Additional data
 */
function sola_donation_log($message, $data = array()) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Sola Donation] ' . $message . ' | Data: ' . print_r($data, true));
    }
}

// Register custom post type for recurring donations
function sola_donation_register_cpt() {
    register_post_type('sola_recurring_donation', array(
        'labels' => array(
            'name' => 'Recurring Donations',
            'singular_name' => 'Recurring Donation'
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'sola-donation-settings',
        'capability_type' => 'post',
        'supports' => array('title'),
        'menu_icon' => 'dashicons-heart'
    ));
}
add_action('init', 'sola_donation_register_cpt');
