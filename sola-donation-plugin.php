<?php
/**
 * Plugin Name: Sola Donation Plugin
 * Plugin URI: https://solapayments.com
 * Description: Professional bilingual (Hebrew/English) donation plugin with Sola Payments integration
 * Version: 1.3.2
 * Author: Meir Tedgi
 * Author URI: https://yourwebsite.com
 * Text Domain: sola-donation
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SOLA_DONATION_VERSION', '1.3.2');
define('SOLA_DONATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SOLA_DONATION_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Plugin activation hook
 */
function sola_donation_activate() {
    // Set default options
    $default_options = array(
        'sandbox_mode' => true,
        'sandbox_key' => '',
        'production_key' => '',
        'redirect_url' => '',
        'webhook_url' => '',
        'form_settings' => array(
            'preset_amounts' => array(
                'USD' => array(10, 25, 50, 100),
                'CAD' => array(10, 25, 50, 100),
                'EUR' => array(10, 25, 50, 100),
                'GBP' => array(10, 25, 50, 100)
            ),
            'enabled_currencies' => array('USD', 'CAD', 'EUR', 'GBP'),
            'default_currency' => 'USD',
            'required_fields' => array(
                'firstName' => true,
                'lastName' => true,
                'phone' => true,
                'email' => true,
                'address' => true,
                'taxId' => false
            )
        )
    );
    
    if (!get_option('sola_donation_settings')) {
        add_option('sola_donation_settings', $default_options);
    }
}
register_activation_hook(__FILE__, 'sola_donation_activate');

/**
 * Plugin deactivation hook
 */
function sola_donation_deactivate() {
    // Cleanup if needed
}
register_deactivation_hook(__FILE__, 'sola_donation_deactivate');

/**
 * Add admin menu
 */
function sola_donation_admin_menu() {
    add_menu_page(
        __('Sola Donations', 'sola-donation'),
        __('Sola Donations', 'sola-donation'),
        'manage_options',
        'sola-donation-settings',
        'sola_donation_settings_page',
        'dashicons-heart',
        30
    );
}
add_action('admin_menu', 'sola_donation_admin_menu');

/**
 * Settings page callback
 */
function sola_donation_settings_page() {
    require_once SOLA_DONATION_PLUGIN_DIR . 'admin/settings-page.php';
}

/**
 * Register settings
 */
function sola_donation_register_settings() {
    register_setting('sola_donation_settings_group', 'sola_donation_settings', 'sola_donation_sanitize_settings');
}
add_action('admin_init', 'sola_donation_register_settings');

/**
 * Sanitize settings
 */
function sola_donation_sanitize_settings($input) {
    $sanitized = array();
    
    $sanitized['sandbox_mode'] = isset($input['sandbox_mode']) ? true : false;
    $sanitized['sandbox_key'] = sanitize_text_field($input['sandbox_key']);
    $sanitized['production_key'] = sanitize_text_field($input['production_key']);
    $sanitized['redirect_url'] = esc_url_raw($input['redirect_url']);
    $sanitized['webhook_url'] = esc_url_raw($input['webhook_url']);
    
    // Sanitize form settings
    $sanitized['form_settings'] = array();
    
    // Preset amounts
    $sanitized['form_settings']['preset_amounts'] = array();
    foreach (array('USD', 'CAD', 'EUR', 'GBP') as $currency) {
        $amounts = array();
        for ($i = 1; $i <= 4; $i++) {
            $key = 'preset_amount_' . $currency . '_' . $i;
            if (isset($input[$key]) && is_numeric($input[$key]) && $input[$key] > 0) {
                $amounts[] = floatval($input[$key]);
            }
        }
        // Ensure we have at least one amount
        if (empty($amounts)) {
            $amounts = array(10, 25, 50, 100); // Default amounts
        }
        $sanitized['form_settings']['preset_amounts'][$currency] = $amounts;
    }
    
    // Enabled currencies - at least one must be enabled
    $enabled_currencies = array();
    foreach (array('USD', 'CAD', 'EUR', 'GBP') as $currency) {
        if (isset($input['enabled_currency_' . $currency])) {
            $enabled_currencies[] = $currency;
        }
    }
    if (empty($enabled_currencies)) {
        $enabled_currencies = array('USD'); // Default to USD if none selected
    }
    $sanitized['form_settings']['enabled_currencies'] = $enabled_currencies;
    
    // Default currency
    $default_currency = isset($input['default_currency']) ? sanitize_text_field($input['default_currency']) : 'USD';
    if (!in_array($default_currency, $enabled_currencies)) {
        $default_currency = $enabled_currencies[0]; // Use first enabled currency
    }
    $sanitized['form_settings']['default_currency'] = $default_currency;
    
    // Required fields
    $sanitized['form_settings']['required_fields'] = array(
        'firstName' => isset($input['required_firstName']) ? true : false,
        'lastName' => isset($input['required_lastName']) ? true : false,
        'phone' => isset($input['required_phone']) ? true : false,
        'email' => isset($input['required_email']) ? true : false,
        'address' => isset($input['required_address']) ? true : false,
        'taxId' => isset($input['required_taxId']) ? true : false
    );
    
    // Validate API key if provided
    $key_to_test = $sanitized['sandbox_mode'] ? $sanitized['sandbox_key'] : $sanitized['production_key'];
    
    if (!empty($key_to_test)) {
        $validation_result = sola_donation_validate_api_key($key_to_test, $sanitized['sandbox_mode']);
        set_transient('sola_donation_api_validation', $validation_result, 30);
    }
    
    return $sanitized;
}

/**
 * Validate API key by testing connection to Sola Payments
 */
function sola_donation_validate_api_key($xKey, $is_sandbox = true) {
    $endpoint = 'https://x1.cardknox.com/gatewayjson';
    
    // Test request - just verify the key is valid
    $request_data = array(
        'xKey' => $xKey,
        'xVersion' => '5.0.0',
        'xSoftwareName' => 'SolaDonation',
        'xSoftwareVersion' => SOLA_DONATION_VERSION,
        'xCommand' => 'cc:sale',
        'xAmount' => '0.00' // Zero dollar auth to test key
    );
    
    $response = wp_remote_post($endpoint, array(
        'body' => $request_data,
        'timeout' => 15,
        'sslverify' => true
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => __('Could not connect to Sola Payments. Please check your internet connection.', 'sola-donation')
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    // Check if key is valid (even if transaction fails due to zero amount, key should be recognized)
    if (isset($data['xError']) && (
        strpos($data['xError'], 'Invalid xKey') !== false ||
        strpos($data['xError'], 'Authentication') !== false ||
        strpos($data['xError'], 'not authorized') !== false
    )) {
        return array(
            'success' => false,
            'message' => __('Invalid API Key. Please check your key and try again.', 'sola-donation')
        );
    }
    
    // If we got here, the key is at least recognized
    $mode = $is_sandbox ? __('Sandbox', 'sola-donation') : __('Production', 'sola-donation');
    return array(
        'success' => true,
        'message' => sprintf(__('API Key valid! Connected to Sola Payments (%s mode).', 'sola-donation'), $mode)
    );
}

/**
 * Enqueue admin styles
 */
function sola_donation_admin_styles($hook) {
    if ($hook !== 'toplevel_page_sola-donation-settings') {
        return;
    }
    
    wp_enqueue_style(
        'sola-donation-admin',
        SOLA_DONATION_PLUGIN_URL . 'admin/admin-styles.css',
        array(),
        SOLA_DONATION_VERSION
    );
    
    wp_enqueue_script(
        'sola-donation-admin',
        SOLA_DONATION_PLUGIN_URL . 'admin/admin-script.js',
        array('jquery'),
        SOLA_DONATION_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'sola_donation_admin_styles');

/**
 * Enqueue frontend styles and scripts
 */
function sola_donation_enqueue_assets() {
    wp_enqueue_style(
        'sola-donation-form',
        SOLA_DONATION_PLUGIN_URL . 'public/form-styles.css',
        array(),
        SOLA_DONATION_VERSION
    );
    
    wp_enqueue_script(
        'sola-donation-form',
        SOLA_DONATION_PLUGIN_URL . 'public/form-script.js',
        array('jquery'),
        SOLA_DONATION_VERSION,
        true
    );
    
    // Localize script with AJAX URL and nonce
    $settings = get_option('sola_donation_settings');
    $form_settings = isset($settings['form_settings']) ? $settings['form_settings'] : array(
        'preset_amounts' => array(
            'USD' => array(10, 25, 50, 100),
            'CAD' => array(10, 25, 50, 100),
            'EUR' => array(10, 25, 50, 100),
            'GBP' => array(10, 25, 50, 100)
        ),
        'enabled_currencies' => array('USD', 'CAD', 'EUR', 'GBP'),
        'default_currency' => 'USD',
        'required_fields' => array(
            'firstName' => true,
            'lastName' => true,
            'phone' => true,
            'email' => true,
            'address' => true,
            'taxId' => false
        )
    );
    
    wp_localize_script('sola-donation-form', 'solaDonation', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sola_donation_nonce'),
        'pluginUrl' => SOLA_DONATION_PLUGIN_URL,
        'formSettings' => $form_settings
    ));
}
add_action('wp_enqueue_scripts', 'sola_donation_enqueue_assets');

/**
 * Register shortcode
 */
function sola_donation_form_shortcode($atts) {
    ob_start();
    require SOLA_DONATION_PLUGIN_DIR . 'public/form-template.php';
    return ob_get_clean();
}
add_shortcode('sola_donation_form', 'sola_donation_form_shortcode');

/**
 * AJAX handler for form submission
 */
function sola_donation_process_form() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sola_donation_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }
    
    // Include API handler
    require_once SOLA_DONATION_PLUGIN_DIR . 'includes/api-handler.php';
    
    // Get form data
    $form_data = array(
        'firstName' => sanitize_text_field($_POST['firstName']),
        'lastName' => sanitize_text_field($_POST['lastName']),
        'phone' => sanitize_text_field($_POST['phone']),
        'email' => sanitize_email($_POST['email']),
        'address' => sanitize_text_field($_POST['address']),
        'currency' => sanitize_text_field($_POST['currency']),
        'donationType' => sanitize_text_field($_POST['donationType']),
        'amount' => floatval($_POST['amount']),
        'chargeDay' => isset($_POST['chargeDay']) ? intval($_POST['chargeDay']) : null,
        'chargeNow' => isset($_POST['chargeNow']) ? (bool)$_POST['chargeNow'] : false,
        'cardNumber' => sanitize_text_field($_POST['cardNumber']),
        'expiry' => sanitize_text_field($_POST['expiry']),
        'cvv' => sanitize_text_field($_POST['cvv'])
    );
    
    // Process payment
    $result = sola_donation_process_payment($form_data);
    
    if ($result['success']) {
        // Send webhook if configured
        $settings = get_option('sola_donation_settings');
        if (!empty($settings['webhook_url'])) {
            require_once SOLA_DONATION_PLUGIN_DIR . 'includes/webhook-handler.php';
            sola_donation_send_webhook($settings['webhook_url'], $result['data'], $form_data);
        }
        
        wp_send_json_success(array(
            'message' => $result['message'],
            'redirectUrl' => !empty($settings['redirect_url']) ? $settings['redirect_url'] : ''
        ));
    } else {
        // Include debug info if WP_DEBUG is on
        $error_response = array('message' => $result['message']);
        if (defined('WP_DEBUG') && WP_DEBUG && isset($result['debug'])) {
            $error_response['debug'] = $result['debug'];
        }
        wp_send_json_error($error_response);
    }
}
add_action('wp_ajax_sola_donation_process', 'sola_donation_process_form');
add_action('wp_ajax_nopriv_sola_donation_process', 'sola_donation_process_form');

/**
 * Load translations
 */
function sola_donation_load_textdomain() {
    load_plugin_textdomain('sola-donation', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'sola_donation_load_textdomain');
