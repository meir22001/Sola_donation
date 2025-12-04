<?php
/**
 * COMPREHENSIVE DEBUG SCRIPT
 * This will show us EXACTLY what's happening at each step
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h1 { color: #023047; }
h2 { color: #F49431; border-bottom: 2px solid #F49431; padding-bottom: 10px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
pre { background: #f9f9f9; padding: 15px; border-radius: 4px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
td, th { padding: 8px; border: 1px solid #ddd; text-align: left; }
th { background: #F49431; color: white; }
</style>";

echo "<h1>🔍 Sola Donation - Complete System Diagnostic</h1>";

// ============================================
// STEP 1: Check Database
// ============================================
echo "<div class='section'>";
echo "<h2>1️⃣ Database Check</h2>";

$settings = get_option('sola_donation_settings');

if (!$settings) {
    echo "<p class='error'>❌ ERROR: No settings found in database!</p>";
    echo "<p>Settings option 'sola_donation_settings' does not exist.</p>";
} else {
    echo "<p class='success'>✓ Settings exist in database</p>";
    
    // Check if form_settings exists
    if (!isset($settings['form_settings'])) {
        echo "<p class='error'>❌ CRITICAL: form_settings is MISSING from database!</p>";
        echo "<p>This is the root cause. The settings were never added to the database.</p>";
    } else {
        echo "<p class='success'>✓ form_settings exists</p>";
        
        // Detailed check
        echo "<table>";
        echo "<tr><th>Setting</th><th>Status</th><th>Value</th></tr>";
        
        $checks = [
            'preset_amounts' => isset($settings['form_settings']['preset_amounts']),
            'enabled_currencies' => isset($settings['form_settings']['enabled_currencies']),
            'default_currency' => isset($settings['form_settings']['default_currency']),
            'required_fields' => isset($settings['form_settings']['required_fields'])
        ];
        
        foreach ($checks as $key => $exists) {
            $status = $exists ? "<span class='success'>✓ Exists</span>" : "<span class='error'>❌ Missing</span>";
            $value = $exists ? json_encode($settings['form_settings'][$key]) : 'N/A';
            echo "<tr><td>{$key}</td><td>{$status}</td><td>" . esc_html($value) . "</td></tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Full Database Content:</h3>";
    echo "<pre>" . print_r($settings, true) . "</pre>";
}

echo "</div>";

// ============================================
// STEP 2: Check if migration function runs
// ============================================
echo "<div class='section'>";
echo "<h2>2️⃣ Migration Function Test</h2>";

// Manually run the migration function
if (function_exists('sola_donation_ensure_form_settings')) {
    echo "<p class='success'>✓ Migration function exists</p>";
    echo "<p>Running migration function now...</p>";
    
    sola_donation_ensure_form_settings();
    
    // Re-check settings
    $settings_after = get_option('sola_donation_settings');
    if (isset($settings_after['form_settings'])) {
        echo "<p class='success'>✓ Migration successful! form_settings now exists.</p>";
    } else {
        echo "<p class='error'>❌ Migration failed! form_settings still missing.</p>";
    }
} else {
    echo "<p class='error'>❌ Migration function does not exist!</p>";
}

echo "</div>";

// ============================================
// STEP 3: Check Template File
// ============================================
echo "<div class='section'>";
echo "<h2>3️⃣ Form Template Check</h2>";

$template_file = SOLA_DONATION_PLUGIN_DIR . 'public/form-template.php';
if (file_exists($template_file)) {
    echo "<p class='success'>✓ Template file exists</p>";
    echo "<p>Path: " . esc_html($template_file) . "</p>";
    
    // Check if template loads settings
    $template_content = file_get_contents($template_file);
    
    if (strpos($template_content, 'get_option(\'sola_donation_settings\')') !== false) {
        echo "<p class='success'>✓ Template calls get_option() to load settings</p>";
    } else {
        echo "<p class='error'>❌ Template does NOT load settings from database!</p>";
    }
    
    if (strpos($template_content, '$preset_amounts') !== false) {
        echo "<p class='success'>✓ Template uses \$preset_amounts variable</p>";
    } else {
        echo "<p class='warning'>⚠ Template doesn't seem to use \$preset_amounts</p>";
    }
    
    if (strpos($template_content, '$enabled_currencies') !== false) {
        echo "<p class='success'>✓ Template uses \$enabled_currencies variable</p>";
    } else {
        echo "<p class='warning'>⚠ Template doesn't seem to use \$enabled_currencies</p>";
    }
} else {
    echo "<p class='error'>❌ Template file not found!</p>";
}

echo "</div>";

// ============================================
// STEP 4: Check JavaScript Localization
// ============================================
echo "<div class='section'>";
echo "<h2>4️⃣ JavaScript Localization Check</h2>";

if (function_exists('sola_donation_enqueue_assets')) {
    echo "<p class='success'>✓ Enqueue function exists</p>";
    
    // Check if it passes formSettings
    $plugin_file = SOLA_DONATION_PLUGIN_DIR . 'sola-donation-plugin.php';
    $plugin_content = file_get_contents($plugin_file);
    
    if (strpos($plugin_content, "'formSettings' => \$form_settings") !== false) {
        echo "<p class='success'>✓ JavaScript receives formSettings via wp_localize_script</p>";
    } else {
        echo "<p class='error'>❌ formSettings not passed to JavaScript!</p>";
    }
} else {
    echo "<p class='error'>❌ Enqueue function not found!</p>";
}

echo "</div>";

// ============================================
// STEP 5: Action Required
// ============================================
echo "<div class='section'>";
echo "<h2>5️⃣ Action Required</h2>";

$final_settings = get_option('sola_donation_settings');

if (!isset($final_settings['form_settings'])) {
    echo "<div style='background: #fff3cd; border: 2px solid #ffa500; padding: 20px; border-radius: 8px;'>";
    echo "<h3 style='color: #ff6600; margin-top: 0;'>⚠️ IMMEDIATE ACTION REQUIRED</h3>";
    echo "<p><strong>The database does NOT contain form_settings!</strong></p>";
    echo "<p>You need to:</p>";
    echo "<ol>";
    echo "<li>Go to WordPress Admin → Plugins</li>";
    echo "<li>Deactivate 'Sola Donation Plugin'</li>";
    echo "<li>Activate it again</li>";
    echo "</ol>";
    echo "<p>OR run the migration script at: <code>your-site.com/wp-content/plugins/sola-donation/migrate-settings.php</code></p>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px; border-radius: 8px;'>";
    echo "<h3 style='color: #28a745; margin-top: 0;'>✅ Database is OK!</h3>";
    echo "<p>If the form still doesn't show correct values, the problem is in:</p>";
    echo "<ul>";
    echo "<li>Browser cache - try hard refresh (Ctrl+Shift+R)</li>";
    echo "<li>WordPress cache plugin - clear cache</li>";
    echo "<li>Server cache - clear server cache</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>";

echo "<hr style='margin: 40px 0;'>";
echo "<p style='text-align: center; color: #666;'>Diagnostic complete. Scroll up to see results.</p>";
