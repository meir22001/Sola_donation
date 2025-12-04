<?php
/**
 * Sola API Test Script
 * Run this from command line: php test-api.php YOUR_API_KEY
 * Or access via browser with proper WordPress context
 */

// For command line testing
if (php_sapi_name() === 'cli') {
    if (!isset($argv[1])) {
        echo "Usage: php test-api.php YOUR_API_KEY\n";
        exit(1);
    }
    $api_key = $argv[1];
} else {
    // For WordPress context - get from settings
    if (!defined('ABSPATH')) {
        die('This script must be run from WordPress context or CLI');
    }
    $settings = get_option('sola_donation_settings');
    $is_sandbox = isset($settings['sandbox_mode']) && $settings['sandbox_mode'];
    $api_key = $is_sandbox ? $settings['sandbox_key'] : $settings['production_key'];
}

echo "=== Sola API Connection Test ===\n\n";
echo "API Key (first 10 chars): " . substr($api_key, 0, 10) . "...\n";
echo "API Key length: " . strlen($api_key) . "\n\n";

// Test 1: Simple cc:sale request
$api_url = 'https://x1.cardknox.com/gateway';

$request_data = array(
    'xKey' => $api_key,
    'xVersion' => '5.0.0',
    'xSoftwareName' => 'Sola Donation Plugin Test',
    'xSoftwareVersion' => '1.3.1',
    'xCommand' => 'cc:sale',
    'xAmount' => '1.00',
    'xCardNum' => '4111111111111111',
    'xExp' => '1225',
    'xCVV' => '123',
    'xBillFirstName' => 'Test',
    'xBillLastName' => 'User',
    'xEmail' => 'test@example.com',
    'xBillPhone' => '1234567890',
    'xBillStreet' => '123 Test St',
    'xCurrency' => 'USD',
    'xInvoice' => 'TEST-' . time(),
    'xDescription' => 'API Test Transaction',
    'xAllowDuplicate' => 'true'
);

echo "Sending test request to: $api_url\n";
echo "Command: cc:sale\n";
echo "Amount: $1.00\n";
echo "Card: 4111111111111111\n\n";

// Use cURL for more detailed response
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded'
));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "=== Response ===\n";
echo "HTTP Code: $http_code\n";

if ($curl_error) {
    echo "cURL Error: $curl_error\n";
} else {
    echo "Raw Response:\n$response\n\n";
    
    $result = json_decode($response, true);
    if ($result) {
        echo "=== Parsed Response ===\n";
        echo "xResult: " . (isset($result['xResult']) ? $result['xResult'] : 'N/A') . "\n";
        echo "xStatus: " . (isset($result['xStatus']) ? $result['xStatus'] : 'N/A') . "\n";
        echo "xError: " . (isset($result['xError']) ? $result['xError'] : 'N/A') . "\n";
        echo "xErrorCode: " . (isset($result['xErrorCode']) ? $result['xErrorCode'] : 'N/A') . "\n";
        echo "xRefNum: " . (isset($result['xRefNum']) ? $result['xRefNum'] : 'N/A') . "\n";
        echo "xToken: " . (isset($result['xToken']) ? $result['xToken'] : 'N/A') . "\n";
        
        if (isset($result['xResult'])) {
            if ($result['xResult'] === 'A') {
                echo "\n✅ SUCCESS! Transaction Approved!\n";
            } elseif ($result['xResult'] === 'D') {
                echo "\n❌ DECLINED: " . ($result['xError'] ?? 'Unknown reason') . "\n";
            } elseif ($result['xResult'] === 'E') {
                echo "\n❌ ERROR: " . ($result['xError'] ?? 'Unknown error') . "\n";
            }
        }
    } else {
        echo "Failed to parse JSON response\n";
    }
}

echo "\n=== End Test ===\n";
