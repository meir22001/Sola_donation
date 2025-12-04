<?php
/**
 * FORCE UPDATE - Run this once to fix the database
 * Access via: your-site.com/wp-content/plugins/sola-donation/force-update.php
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator.');
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Sola Donation - Force Update</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #F49431; margin-top: 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0; }
        pre { background: #f9f9f9; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .btn { background: #F49431; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; }
        .btn:hover { background: #ff9d47; }
    </style>
</head>
<body>
    <div class='box'>
        <h1>🔧 Sola Donation - Force Database Update</h1>";

// Get current settings
$current_settings = get_option('sola_donation_settings');

echo "<h2>Current Status:</h2>";
if (!$current_settings) {
    echo "<div class='error'>❌ No settings found in database!</div>";
    exit;
}

if (isset($current_settings['form_settings'])) {
    echo "<div class='success'>✅ form_settings already exists! No update needed.</div>";
    echo "<pre>" . print_r($current_settings['form_settings'], true) . "</pre>";
    echo "<p><a href='" . admin_url('admin.php?page=sola-donation-settings') . "' class='btn'>Go to Settings</a></p>";
} else {
    echo "<div class='error'>❌ form_settings is MISSING!</div>";
    
    // Add form_settings
    $current_settings['form_settings'] = array(
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
    
    $result = update_option('sola_donation_settings', $current_settings);
    
    if ($result) {
        // Verify it worked
        $verify = get_option('sola_donation_settings');
        if (isset($verify['form_settings'])) {
            echo "<div class='success'>";
            echo "<h3>✅ SUCCESS! Database updated successfully!</h3>";
            echo "<p><strong>form_settings has been added to your database.</strong></p>";
            echo "<p>You can now:</p>";
            echo "<ol>";
            echo "<li>Go to <a href='" . admin_url('admin.php?page=sola-donation-settings') . "'>Settings Page</a></li>";
            echo "<li>Configure your preset amounts, currencies, and field requirements</li>";
            echo "<li>View your donation form to see the changes</li>";
            echo "</ol>";
            echo "</div>";
            
            echo "<h3>New Settings:</h3>";
            echo "<pre>" . print_r($verify['form_settings'], true) . "</pre>";
            
            echo "<p><strong>⚠️ IMPORTANT:</strong> Delete this file (force-update.php) for security!</p>";
        } else {
            echo "<div class='error'>❌ Update failed! form_settings still missing.</div>";
        }
    } else {
        echo "<div class='error'>❌ Failed to update database. No changes were made.</div>";
    }
}

echo "    </div>
</body>
</html>";
