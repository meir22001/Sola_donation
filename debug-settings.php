<?php
/**
 * Debug script to check what's actually stored in the database
 * Place this file in wp-content/plugins/sola-donation/ and access it via browser
 */

// Load WordPress
require_once('../../../wp-load.php');

// Get the settings
$settings = get_option('sola_donation_settings');

echo "<h1>Sola Donation Settings Debug</h1>";
echo "<h2>Current Settings in Database:</h2>";
echo "<pre>";
var_dump($settings);
echo "</pre>";

echo "<h2>form_settings exists:</h2>";
echo isset($settings['form_settings']) ? 'YES' : 'NO';

echo "<h2>Preset Amounts:</h2>";
echo "<pre>";
var_dump(isset($settings['form_settings']['preset_amounts']) ? $settings['form_settings']['preset_amounts'] : 'NOT SET');
echo "</pre>";

echo "<h2>Enabled Currencies:</h2>";
echo "<pre>";
var_dump(isset($settings['form_settings']['enabled_currencies']) ? $settings['form_settings']['enabled_currencies'] : 'NOT SET');
echo "</pre>";

echo "<h2>Required Fields:</h2>";
echo "<pre>";
var_dump(isset($settings['form_settings']['required_fields']) ? $settings['form_settings']['required_fields'] : 'NOT SET');
echo "</pre>";
