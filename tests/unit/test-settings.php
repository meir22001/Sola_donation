<?php
/**
 * Settings Tests
 */

class Test_Settings extends WP_UnitTestCase {

    /**
     * Test settings sanitization
     */
    public function test_settings_sanitization() {
        $input = array(
            'sandbox_mode' => '1',
            'sandbox_key' => '  test_key_with_spaces  ',
            'production_key' => '<script>alert("xss")</script>',
            'redirect_url' => 'javascript:alert("xss")',
            'webhook_url' => 'https://example.com/webhook?param=value'
        );
        
        $sanitized = sola_donation_sanitize_settings($input);
        
        // Checkbox should be boolean
        $this->assertTrue($sanitized['sandbox_mode']);
        
        // Keys should be trimmed
        $this->assertEquals('test_key_with_spaces', $sanitized['sandbox_key']);
        
        // Script tags should be removed
        $this->assertStringNotContainsString('<script>', $sanitized['production_key']);
        
        // JavaScript URLs should be sanitized
        $this->assertStringNotContainsString('javascript:', $sanitized['redirect_url']);
        
        // Valid URL should be preserved
        $this->assertEquals('https://example.com/webhook?param=value', $sanitized['webhook_url']);
    }
    
    /**
     * Test unchecked sandbox mode
     */
    public function test_unchecked_sandbox_mode() {
        $input = array(
            'sandbox_key' => 'test',
            'production_key' => 'test',
            'redirect_url' => '',
            'webhook_url' => ''
        );
        
        $sanitized = sola_donation_sanitize_settings($input);
        
        // Unchecked checkbox should be false
        $this->assertFalse($sanitized['sandbox_mode']);
    }
    
    /**
     * Test empty URLs
     */
    public function test_empty_urls() {
        $input = array(
            'sandbox_mode' => true,
            'sandbox_key' => 'test',
            'production_key' => 'test',
            'redirect_url' => '',
            'webhook_url' => ''
        );
        
        $sanitized = sola_donation_sanitize_settings($input);
        
        $this->assertEmpty($sanitized['redirect_url']);
        $this->assertEmpty($sanitized['webhook_url']);
    }
    
    /**
     * Test settings retrieval
     */
    public function test_settings_retrieval() {
        $test_settings = array(
            'sandbox_mode' => true,
            'sandbox_key' => 'sk_test_123',
            'production_key' => 'pk_live_456',
            'redirect_url' => 'https://example.com/thank-you',
            'webhook_url' => 'https://example.com/webhook'
        );
        
        update_option('sola_donation_settings', $test_settings);
        
        $retrieved = get_option('sola_donation_settings');
        
        $this->assertEquals($test_settings, $retrieved);
        $this->assertTrue($retrieved['sandbox_mode']);
        $this->assertEquals('sk_test_123', $retrieved['sandbox_key']);
    }
    
    /**
     * Test settings update
     */
    public function test_settings_update() {
        $initial_settings = array(
            'sandbox_mode' => true,
            'sandbox_key' => 'old_key',
            'production_key' => '',
            'redirect_url' => '',
            'webhook_url' => ''
        );
        
        update_option('sola_donation_settings', $initial_settings);
        
        $updated_settings = array(
            'sandbox_mode' => false,
            'sandbox_key' => 'old_key',
            'production_key' => 'new_prod_key',
            'redirect_url' => 'https://example.com/thanks',
            'webhook_url' => 'https://example.com/hook'
        );
        
        update_option('sola_donation_settings', $updated_settings);
        
        $retrieved = get_option('sola_donation_settings');
        
        $this->assertFalse($retrieved['sandbox_mode']);
        $this->assertEquals('new_prod_key', $retrieved['production_key']);
        $this->assertEquals('https://example.com/thanks', $retrieved['redirect_url']);
    }
}
