<?php
/**
 * Webhook Handler
 * Sends transaction data to configured webhook URL
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Send webhook notification
 *
 * @param string $webhook_url Webhook URL
 * @param array $transaction_data Transaction data from Sola
 * @param array $form_data Original form data
 * @return bool Success status
 */
function sola_donation_send_webhook($webhook_url, $transaction_data, $form_data) {
    if (empty($webhook_url)) {
        return false;
    }
    
    // Prepare webhook payload
    $payload = array(
        'event' => 'donation_successful',
        'timestamp' => current_time('mysql'),
        'donation' => array(
            'type' => $form_data['donationType'],
            'amount' => $form_data['amount'],
            'currency' => $form_data['currency']
        ),
        'donor' => array(
            'firstName' => $form_data['firstName'],
            'lastName' => $form_data['lastName'],
            'email' => $form_data['email'],
            'phone' => $form_data['phone'],
            'address' => $form_data['address']
        ),
        'transaction' => array(
            'refNum' => isset($transaction_data['refNum']) ? $transaction_data['refNum'] : '',
            'authAmount' => isset($transaction_data['authAmount']) ? $transaction_data['authAmount'] : '',
            'authCode' => isset($transaction_data['authCode']) ? $transaction_data['authCode'] : '',
            'maskedCard' => isset($transaction_data['maskedCard']) ? $transaction_data['maskedCard'] : '',
            'cardType' => isset($transaction_data['cardType']) ? $transaction_data['cardType'] : ''
        )
    );
    
    // Add recurring info if applicable
    if ($form_data['donationType'] === 'monthly') {
        $payload['donation']['recurring'] = array(
            'chargeDay' => isset($form_data['chargeDay']) ? $form_data['chargeDay'] : null,
            'chargeNow' => isset($form_data['chargeNow']) ? $form_data['chargeNow'] : false
        );
    }
    
    // Log webhook attempt
    sola_donation_log('Sending webhook', array(
        'url' => $webhook_url,
        'event' => 'donation_successful'
    ));
    
    // Send webhook with retry logic
    $max_retries = 3;
    $retry_count = 0;
    $success = false;
    
    while ($retry_count < $max_retries && !$success) {
        $response = wp_remote_post($webhook_url, array(
            'body' => json_encode($payload),
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Sola-Donation-Event' => 'donation_successful',
                'X-Sola-Donation-Signature' => sola_donation_generate_signature($payload)
            ),
            'timeout' => 15
        ));
        
        if (!is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code >= 200 && $response_code < 300) {
                $success = true;
                sola_donation_log('Webhook sent successfully', array(
                    'url' => $webhook_url,
                    'response_code' => $response_code,
                    'retry_count' => $retry_count
                ));
            } else {
                sola_donation_log('Webhook failed', array(
                    'url' => $webhook_url,
                    'response_code' => $response_code,
                    'retry_count' => $retry_count
                ));
            }
        } else {
            sola_donation_log('Webhook error', array(
                'url' => $webhook_url,
                'error' => $response->get_error_message(),
                'retry_count' => $retry_count
            ));
        }
        
        $retry_count++;
        
        if (!$success && $retry_count < $max_retries) {
            // Wait before retry (exponential backoff)
            sleep(pow(2, $retry_count));
        }
    }
    
    return $success;
}

/**
 * Generate webhook signature for verification
 *
 * @param array $payload Webhook payload
 * @return string Signature hash
 */
function sola_donation_generate_signature($payload) {
    $secret = get_option('sola_donation_webhook_secret', wp_salt());
    return hash_hmac('sha256', json_encode($payload), $secret);
}
