<?php
/**
 * Settings Page Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('sola_donation_settings');

// Check for API validation result
$api_validation = get_transient('sola_donation_api_validation');
if ($api_validation !== false) {
    delete_transient('sola_donation_api_validation');
}

$sandbox_mode = isset($settings['sandbox_mode']) ? $settings['sandbox_mode'] : true;
$sandbox_key = isset($settings['sandbox_key']) ? $settings['sandbox_key'] : '';
$production_key = isset($settings['production_key']) ? $settings['production_key'] : '';
$redirect_url = isset($settings['redirect_url']) ? $settings['redirect_url'] : '';
$webhook_url = isset($settings['webhook_url']) ? $settings['webhook_url'] : '';

// Handle form submission
if (isset($_POST['sola_donation_save'])) {
    check_admin_referer('sola_donation_settings_nonce');
    
    $new_settings = array(
        'sandbox_mode' => isset($_POST['sandbox_mode']) ? true : false,
        'sandbox_key' => sanitize_text_field($_POST['sandbox_key']),
        'production_key' => sanitize_text_field($_POST['production_key']),
        'redirect_url' => esc_url_raw($_POST['redirect_url']),
        'webhook_url' => esc_url_raw($_POST['webhook_url'])
    );
    
    update_option('sola_donation_settings', $new_settings);
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'sola-donation') . '</p></div>';
    
    // Update local variables
    $settings = $new_settings;
    $sandbox_mode = $settings['sandbox_mode'];
    $sandbox_key = $settings['sandbox_key'];
    $production_key = $settings['production_key'];
    $redirect_url = $settings['redirect_url'];
    $webhook_url = $settings['webhook_url'];
}
?>

<div class="wrap sola-donation-settings">
    <h1><?php echo esc_html__('Sola Donation Settings', 'sola-donation'); ?></h1>
    
    <?php if ($api_validation): ?>
        <?php if ($api_validation['success']): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong><?php echo esc_html__('Success!', 'sola-donation'); ?></strong> <?php echo esc_html($api_validation['message']); ?></p>
            </div>
        <?php else: ?>
            <div class="notice notice-error is-dismissible">
                <p><strong><?php echo esc_html__('Error!', 'sola-donation'); ?></strong> <?php echo esc_html($api_validation['message']); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('sola_donation_settings_nonce'); ?>
        
        <!-- API Configuration -->
        <div class="sola-settings-section">
            <h2><?php echo esc_html__('API Configuration', 'sola-donation'); ?></h2>
            <p class="description">
                <?php echo esc_html__('Enter your Sola Payments API keys. You can get these from your Sola Payments account.', 'sola-donation'); ?>
                <a href="https://solapayments.com/devsdk/" target="_blank"><?php echo esc_html__('Get Sandbox Key', 'sola-donation'); ?></a>
            </p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sandbox_mode"><?php echo esc_html__('Sandbox Mode', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <label class="sola-toggle">
                            <input type="checkbox" name="sandbox_mode" id="sandbox_mode" value="1" <?php checked($sandbox_mode, true); ?>>
                            <span class="sola-toggle-slider"></span>
                        </label>
                        <p class="description">
                            <?php echo esc_html__('Enable this to use sandbox/test mode. Disable for live transactions.', 'sola-donation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="sandbox_key"><?php echo esc_html__('Sandbox xKey', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="sandbox_key" 
                               id="sandbox_key" 
                               value="<?php echo esc_attr($sandbox_key); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('Enter sandbox xKey', 'sola-donation'); ?>">
                        <p class="description">
                            <?php echo esc_html__('Your Sola Payments sandbox API key for testing.', 'sola-donation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="production_key"><?php echo esc_html__('Production xKey', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="production_key" 
                               id="production_key" 
                               value="<?php echo esc_attr($production_key); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('Enter production xKey', 'sola-donation'); ?>">
                        <p class="description">
                            <?php echo esc_html__('Your Sola Payments production API key for live transactions.', 'sola-donation'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Post-Submission Actions -->
        <div class="sola-settings-section">
            <h2><?php echo esc_html__('Post-Submission Actions', 'sola-donation'); ?></h2>
            <p class="description">
                <?php echo esc_html__('Configure what happens after a successful donation.', 'sola-donation'); ?>
            </p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="redirect_url"><?php echo esc_html__('Redirect URL', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <input type="url" 
                               name="redirect_url" 
                               id="redirect_url" 
                               value="<?php echo esc_attr($redirect_url); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('https://example.com/thank-you', 'sola-donation'); ?>">
                        <p class="description">
                            <?php echo esc_html__('URL to redirect users after successful donation (optional).', 'sola-donation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="webhook_url"><?php echo esc_html__('Webhook URL', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <input type="url" 
                               name="webhook_url" 
                               id="webhook_url" 
                               value="<?php echo esc_attr($webhook_url); ?>" 
                               class="regular-text"
                               placeholder="<?php echo esc_attr__('https://example.com/webhook', 'sola-donation'); ?>">
                        <p class="description">
                            <?php echo esc_html__('URL to send transaction data after successful donation (optional).', 'sola-donation'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Save Button -->
        <p class="submit">
            <button type="submit" name="sola_donation_save" class="button button-primary button-large">
                <?php echo esc_html__('Save Settings', 'sola-donation'); ?>
            </button>
            <button type="submit" name="sola_donation_test_api" class="button button-secondary button-large" style="margin-left: 10px;">
                <?php echo esc_html__('Test API Connection', 'sola-donation'); ?>
            </button>
        </p>
    </form>
    
    <?php
    // Handle API Test
    if (isset($_POST['sola_donation_test_api'])) {
        check_admin_referer('sola_donation_settings_nonce');
        
        $is_sandbox = isset($settings['sandbox_mode']) && $settings['sandbox_mode'];
        $api_key = $is_sandbox ? $settings['sandbox_key'] : $settings['production_key'];
        
        if (empty($api_key)) {
            echo '<div class="notice notice-error"><p><strong>Error:</strong> No API key configured. Please save your API key first.</p></div>';
        } else {
            // Test API
            $api_url = 'https://x1.cardknox.com/gateway';
            
            $request_data = array(
                'xKey' => $api_key,
                'xVersion' => '5.0.0',
                'xSoftwareName' => 'Sola Donation Plugin',
                'xSoftwareVersion' => SOLA_DONATION_VERSION,
                'xCommand' => 'cc:sale',
                'xAmount' => '1.00',
                'xCardNum' => '4111111111111111',
                'xExp' => '1225',
                'xCVV' => '123',
                'xBillFirstName' => 'Test',
                'xBillLastName' => 'User',
                'xEmail' => 'test@example.com',
                'xBillStreet' => '123 Test St',
                'xCurrency' => 'USD',
                'xInvoice' => 'TEST-' . time(),
                'xAllowDuplicate' => 'true'
            );
            
            $response = wp_remote_post($api_url, array(
                'body' => $request_data,
                'timeout' => 30,
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded'
                )
            ));
            
            echo '<div class="sola-settings-section" style="background: #f9f9f9; border-left: 4px solid #0073aa; margin-top: 20px;">';
            echo '<h3>API Test Results</h3>';
            echo '<p><strong>Mode:</strong> ' . ($is_sandbox ? 'Sandbox' : 'Production') . '</p>';
            echo '<p><strong>API Key (first 15 chars):</strong> ' . esc_html(substr($api_key, 0, 15)) . '...</p>';
            echo '<p><strong>API Key Length:</strong> ' . strlen($api_key) . ' characters</p>';
            
            if (is_wp_error($response)) {
                echo '<p style="color: red;"><strong>Connection Error:</strong> ' . esc_html($response->get_error_message()) . '</p>';
            } else {
                $body = wp_remote_retrieve_body($response);
                $http_code = wp_remote_retrieve_response_code($response);
                $result = json_decode($body, true);
                
                echo '<p><strong>HTTP Code:</strong> ' . esc_html($http_code) . '</p>';
                echo '<p><strong>Raw Response:</strong></p>';
                echo '<pre style="background: #fff; padding: 10px; overflow: auto; max-height: 200px;">' . esc_html($body) . '</pre>';
                
                if ($result) {
                    $xResult = isset($result['xResult']) ? $result['xResult'] : 'N/A';
                    $xStatus = isset($result['xStatus']) ? $result['xStatus'] : 'N/A';
                    $xError = isset($result['xError']) ? $result['xError'] : 'N/A';
                    $xErrorCode = isset($result['xErrorCode']) ? $result['xErrorCode'] : 'N/A';
                    
                    echo '<p><strong>xResult:</strong> ' . esc_html($xResult) . '</p>';
                    echo '<p><strong>xStatus:</strong> ' . esc_html($xStatus) . '</p>';
                    echo '<p><strong>xError:</strong> ' . esc_html($xError) . '</p>';
                    echo '<p><strong>xErrorCode:</strong> ' . esc_html($xErrorCode) . '</p>';
                    
                    if ($xResult === 'A') {
                        echo '<p style="color: green; font-weight: bold;">✅ API Connection Successful! Transaction Approved.</p>';
                    } elseif ($xResult === 'D') {
                        echo '<p style="color: orange; font-weight: bold;">⚠️ API Connected but transaction declined: ' . esc_html($xError) . '</p>';
                    } elseif ($xResult === 'E') {
                        echo '<p style="color: red; font-weight: bold;">❌ API Error: ' . esc_html($xError) . '</p>';
                    }
                }
            }
            echo '</div>';
        }
    }
    ?>
    
    <!-- Shortcode Instructions -->
    <div class="sola-settings-section sola-shortcode-info">
        <h2><?php echo esc_html__('How to Use', 'sola-donation'); ?></h2>
        <p><?php echo esc_html__('Add the donation form to any page or post using this shortcode:', 'sola-donation'); ?></p>
        <div class="sola-shortcode-box">
            <code>[sola_donation_form]</code>
            <button class="button button-small sola-copy-shortcode" onclick="navigator.clipboard.writeText('[sola_donation_form]')">
                <?php echo esc_html__('Copy', 'sola-donation'); ?>
            </button>
        </div>
    </div>
</div>
