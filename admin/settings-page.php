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

// Form settings
$form_settings = isset($settings['form_settings']) ? $settings['form_settings'] : array();
$preset_amounts = isset($form_settings['preset_amounts']) ? $form_settings['preset_amounts'] : array(
    'USD' => array(10, 25, 50, 100),
    'CAD' => array(10, 25, 50, 100),
    'EUR' => array(10, 25, 50, 100),
    'GBP' => array(10, 25, 50, 100)
);
$enabled_currencies = isset($form_settings['enabled_currencies']) ? $form_settings['enabled_currencies'] : array('USD', 'CAD', 'EUR', 'GBP');
$default_currency = isset($form_settings['default_currency']) ? $form_settings['default_currency'] : 'USD';
$required_fields = isset($form_settings['required_fields']) ? $form_settings['required_fields'] : array(
    'firstName' => true,
    'lastName' => true,
    'phone' => true,
    'email' => true,
    'address' => true,
    'taxId' => false
);

// Handle form submission
if (isset($_POST['sola_donation_save'])) {
    // Verify nonce
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'sola_donation_settings_nonce')) {
        wp_die(__('Security check failed', 'sola-donation'));
    }
    
    // Log POST data for debugging (will help identify what's being sent)
    error_log('SOLA DONATION SAVE - POST Keys: ' . implode(', ', array_keys($_POST)));
    
    // Use the sanitize function from main plugin file which handles all settings including form_settings
    $new_settings = sola_donation_sanitize_settings($_POST);
    
    // Log what we're about to save
    error_log('SOLA DONATION SAVE - Settings to save: ' . print_r($new_settings, true));
    
    // Save to database
    $updated = update_option('sola_donation_settings', $new_settings);
    
    // Log result
    error_log('SOLA DONATION SAVE - Update result: ' . ($updated ? 'SUCCESS' : 'NO CHANGE'));
    
    // Add a success message
    add_settings_error(
        'sola_donation_messages',
        'sola_donation_message',
        __('Settings saved successfully!', 'sola-donation'),
        'success'
    );
    
    // Set transient to show message after redirect
    set_transient('sola_donation_settings_saved', true, 30);
    
    // Redirect to prevent form resubmission
    wp_redirect(add_query_arg(array(
        'page' => 'sola-donation-settings',
        'settings-updated' => 'true'
    ), admin_url('admin.php')));
    exit;
}
?>

<div class="wrap sola-donation-settings">
    <h1><?php echo esc_html__('Sola Donation Settings', 'sola-donation'); ?></h1>
    
    <?php
    // Show success message if redirected after save
    if (get_transient('sola_donation_settings_saved')) {
        delete_transient('sola_donation_settings_saved');
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'sola-donation') . '</p></div>';
    }
    
    // Show WordPress settings errors
    settings_errors('sola_donation_messages');
    ?>
    
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
        
        <!-- Form Customization -->
        <div class="sola-settings-section">
            <h2><?php echo esc_html__('Form Customization', 'sola-donation'); ?></h2>
            <p class="description">
                <?php echo esc_html__('Customize the donation form preset amounts, enabled currencies, and field requirements.', 'sola-donation'); ?>
            </p>
            
            <table class="form-table">
                <!-- Preset Amounts Section -->
                <tr>
                    <th scope="row">
                        <label><?php echo esc_html__('Preset Amounts', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <div class="sola-currency-tabs">
                            <div class="sola-tab-buttons">
                                <button type="button" class="sola-tab-btn active" data-currency="USD">US$</button>
                                <button type="button" class="sola-tab-btn" data-currency="CAD">CA$</button>
                                <button type="button" class="sola-tab-btn" data-currency="EUR">€</button>
                                <button type="button" class="sola-tab-btn" data-currency="GBP">£</button>
                            </div>
                            
                            <?php foreach (array('USD', 'CAD', 'EUR', 'GBP') as $currency): ?>
                                <div class="sola-tab-content <?php echo $currency === 'USD' ? 'active' : ''; ?>" data-currency="<?php echo $currency; ?>">
                                    <div class="sola-amounts-grid">
                                        <?php 
                                        $amounts = isset($preset_amounts[$currency]) ? $preset_amounts[$currency] : array(10, 25, 50, 100);
                                        for ($i = 0; $i < 4; $i++): 
                                            $value = isset($amounts[$i]) ? $amounts[$i] : '';
                                        ?>
                                            <div class="sola-amount-input-wrapper">
                                                <label><?php echo sprintf(__('Amount %d', 'sola-donation'), $i + 1); ?></label>
                                                <input type="number" 
                                                       name="preset_amount_<?php echo $currency; ?>_<?php echo $i + 1; ?>" 
                                                       value="<?php echo esc_attr($value); ?>" 
                                                       min="0" 
                                                       step="0.01"
                                                       class="small-text"
                                                       placeholder="<?php echo esc_attr__('Amount', 'sola-donation'); ?>">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="description"><?php echo esc_html__('Set preset donation amounts for each currency. Leave empty to skip an amount slot.', 'sola-donation'); ?></p>
                    </td>
                </tr>
                
                <!-- Currency Settings -->
                <tr>
                    <th scope="row">
                        <label><?php echo esc_html__('Enabled Currencies', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <div class="sola-currency-checkboxes">
                            <?php foreach (array('USD' => 'US$', 'CAD' => 'CA$', 'EUR' => '€', 'GBP' => '£') as $curr => $symbol): ?>
                                <label class="sola-checkbox-label-inline">
                                    <input type="checkbox" 
                                           name="enabled_currency_<?php echo $curr; ?>" 
                                           value="1" 
                                           <?php checked(in_array($curr, $enabled_currencies)); ?>>
                                    <span><?php echo $symbol . ' ' . $curr; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="description"><?php echo esc_html__('Select which currencies will be available in the donation form. At least one must be enabled.', 'sola-donation'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_currency"><?php echo esc_html__('Default Currency', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <select name="default_currency" id="default_currency">
                            <?php foreach (array('USD' => 'US$ USD', 'CAD' => 'CA$ CAD', 'EUR' => '€ EUR', 'GBP' => '£ GBP') as $curr => $label): ?>
                                <option value="<?php echo esc_attr($curr); ?>" <?php selected($default_currency, $curr); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo esc_html__('Currency selected by default when the form loads.', 'sola-donation'); ?></p>
                    </td>
                </tr>
                
                <!-- Required Fields -->
                <tr>
                    <th scope="row">
                        <label><?php echo esc_html__('Required Fields', 'sola-donation'); ?></label>
                    </th>
                    <td>
                        <div class="sola-required-fields">
                            <?php 
                            $field_labels = array(
                                'firstName' => __('First Name', 'sola-donation'),
                                'lastName' => __('Last Name', 'sola-donation'),
                                'phone' => __('Phone', 'sola-donation'),
                                'email' => __('Email', 'sola-donation'),
                                'address' => __('Address', 'sola-donation'),
                                'taxId' => __('Tax ID', 'sola-donation')
                            );
                            foreach ($field_labels as $field => $label): 
                                $is_required = isset($required_fields[$field]) ? $required_fields[$field] : true;
                            ?>
                                <div class="sola-field-requirement">
                                    <label class="sola-toggle">
                                        <input type="checkbox" 
                                               name="required_<?php echo $field; ?>" 
                                               value="1" 
                                               <?php checked($is_required, true); ?>>
                                        <span class="sola-toggle-slider"></span>
                                    </label>
                                    <span class="field-label"><?php echo esc_html($label); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="description"><?php echo esc_html__('Toggle which fields are required vs optional. Email is strongly recommended to be required.', 'sola-donation'); ?></p>
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
