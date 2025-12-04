<?php
/**
 * Plugin Activation Tests
 */

class Test_Plugin_Activation extends WP_UnitTestCase {

    /**
     * Test plugin activation
     */
    public function test_plugin_activation() {
        // Activate plugin
        sola_donation_activate();
        
        // Check if default options are set
        $settings = get_option('sola_donation_settings');
        
        $this->assertNotFalse($settings, 'Settings should be created on activation');
        $this->assertArrayHasKey('sandbox_mode', $settings);
        $this->assertArrayHasKey('sandbox_key', $settings);
        $this->assertArrayHasKey('production_key', $settings);
        $this->assertArrayHasKey('redirect_url', $settings);
        $this->assertArrayHasKey('webhook_url', $settings);
    }
    
    /**
     * Test default settings values
     */
    public function test_default_settings_values() {
        delete_option('sola_donation_settings');
        sola_donation_activate();
        
        $settings = get_option('sola_donation_settings');
        
        $this->assertTrue($settings['sandbox_mode'], 'Sandbox mode should be enabled by default');
        $this->assertEmpty($settings['sandbox_key'], 'Sandbox key should be empty by default');
        $this->assertEmpty($settings['production_key'], 'Production key should be empty by default');
        $this->assertEmpty($settings['redirect_url'], 'Redirect URL should be empty by default');
        $this->assertEmpty($settings['webhook_url'], 'Webhook URL should be empty by default');
    }
    
    /**
     * Test that constants are defined
     */
    public function test_constants_defined() {
        $this->assertTrue(defined('SOLA_DONATION_VERSION'), 'VERSION constant should be defined');
        $this->assertTrue(defined('SOLA_DONATION_PLUGIN_DIR'), 'PLUGIN_DIR constant should be defined');
        $this->assertTrue(defined('SOLA_DONATION_PLUGIN_URL'), 'PLUGIN_URL constant should be defined');
    }
    
    /**
     * Test shortcode registration
     */
    public function test_shortcode_registered() {
        $this->assertTrue(shortcode_exists('sola_donation_form'), 'Shortcode should be registered');
    }
    
    /**
     * Test admin menu registration
     */
    public function test_admin_menu_hook() {
        $this->assertEquals(10, has_action('admin_menu', 'sola_donation_admin_menu'), 'Admin menu hook should be registered');
    }
    
    /**
     * Test settings registration
     */
    public function test_settings_registration() {
        $this->assertEquals(10, has_action('admin_init', 'sola_donation_register_settings'), 'Settings registration hook should be registered');
    }
}
