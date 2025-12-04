<?php
/**
 * One-time migration script to force update all settings
 * Access this via browser to manually trigger the update
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Sola Donation Settings Migration</h1>";

// Get current settings
$settings = get_option('sola_donation_settings');

echo "<h2>Before Update:</h2>";
echo "<pre>";
print_r($settings);
echo "</pre>";

// Force add/update form_settings
if ($settings) {
    $settings['form_settings'] = array(
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
    
    $result = update_option('sola_donation_settings', $settings);
    
    echo "<h2>Update Result: " . ($result ? 'SUCCESS' : 'FAILED') . "</h2>";
    
    // Get updated settings
    $updated_settings = get_option('sola_donation_settings');
    
    echo "<h2>After Update:</h2>";
    echo "<pre>";
    print_r($updated_settings);
    echo "</pre>";
    
    echo "<h2 style='color: green;'>✓ Settings Updated Successfully!</h2>";
    echo "<p>You can now go to the admin panel and test the settings.</p>";
    echo "<p><strong>IMPORTANT:</strong> Delete this file (migrate-settings.php) after running it!</p>";
} else {
    echo "<h2 style='color: red;'>ERROR: No existing settings found!</h2>";
}
