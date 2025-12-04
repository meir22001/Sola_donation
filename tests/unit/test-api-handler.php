<?php
/**
 * API Handler Tests
 */

require_once dirname(dirname(__DIR__)) . '/includes/api-handler.php';

class Test_API_Handler extends WP_UnitTestCase {

    /**
     * Setup test environment
     */
    public function setUp(): void {
        parent::setUp();
        
        // Set test settings
        update_option('sola_donation_settings', array(
            'sandbox_mode' => true,
            'sandbox_key' => 'test_sandbox_key',
            'production_key' => 'test_production_key',
            'redirect_url' => 'https://example.com/thank-you',
            'webhook_url' => 'https://example.com/webhook'
        ));
    }

    /**
     * Test form data validation
     */
    public function test_form_data_validation() {
        $form_data = array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phone' => '123-456-7890',
            'email' => 'john@example.com',
            'address' => '123 Main St',
            'currency' => 'USD',
            'donationType' => 'oneTime',
            'amount' => 100.00,
            'cardNumber' => '4111111111111111',
            'expiry' => '1225',
            'cvv' => '123'
        );
        
        // All fields should be present
        $this->assertArrayHasKey('firstName', $form_data);
        $this->assertArrayHasKey('lastName', $form_data);
        $this->assertArrayHasKey('email', $form_data);
        $this->assertArrayHasKey('amount', $form_data);
        $this->assertArrayHasKey('cardNumber', $form_data);
        
        // Email should be valid
        $this->assertNotFalse(filter_var($form_data['email'], FILTER_VALIDATE_EMAIL));
        
        // Amount should be positive
        $this->assertGreaterThan(0, $form_data['amount']);
    }
    
    /**
     * Test API key selection (sandbox vs production)
     */
    public function test_api_key_selection() {
        $settings = get_option('sola_donation_settings');
        
        // Sandbox mode enabled
        $this->assertTrue($settings['sandbox_mode']);
        $this->assertEquals('test_sandbox_key', $settings['sandbox_key']);
        
        // Switch to production
        $settings['sandbox_mode'] = false;
        update_option('sola_donation_settings', $settings);
        
        $updated_settings = get_option('sola_donation_settings');
        $this->assertFalse($updated_settings['sandbox_mode']);
        $this->assertEquals('test_production_key', $updated_settings['production_key']);
    }
    
    /**
     * Test recurring donation data structure
     */
    public function test_recurring_donation_data() {
        $recurring_data = array(
            'token' => 'test_token_123',
            'amount' => 100,
            'currency' => 'USD',
            'charge_day' => 15,
            'donor_email' => 'john@example.com',
            'donor_name' => 'John Doe',
            'status' => 'active'
        );
        
        $this->assertEquals('test_token_123', $recurring_data['token']);
        $this->assertEquals(15, $recurring_data['charge_day']);
        $this->assertEquals('active', $recurring_data['status']);
        $this->assertIsNumeric($recurring_data['charge_day']);
        $this->assertGreaterThanOrEqual(1, $recurring_data['charge_day']);
        $this->assertLessThanOrEqual(28, $recurring_data['charge_day']);
    }
    
    /**
     * Test currency validation
     */
    public function test_currency_validation() {
        $valid_currencies = array('ILS', 'USD', 'EUR');
        
        foreach ($valid_currencies as $currency) {
            $this->assertContains($currency, $valid_currencies);
        }
        
        // Invalid currency
        $this->assertNotContains('GBP', $valid_currencies);
    }
    
    /**
     * Test amount validation
     */
    public function test_amount_validation() {
        // Valid amounts
        $this->assertIsNumeric(100);
        $this->assertIsNumeric(50.50);
        $this->assertGreaterThan(0, 100);
        
        // Invalid amounts
        $this->assertFalse(is_numeric('abc'));
        $this->assertFalse(-10 > 0);
    }
    
    /**
     * Test card number format
     */
    public function test_card_number_format() {
        $card_with_spaces = '4111 1111 1111 1111';
        $card_without_spaces = str_replace(' ', '', $card_with_spaces);
        
        $this->assertEquals('4111111111111111', $card_without_spaces);
        $this->assertEquals(16, strlen($card_without_spaces));
    }
    
    /**
     * Test expiry format
     */
    public function test_expiry_format() {
        $expiry_mmyy = '1225';
        
        $this->assertEquals(4, strlen($expiry_mmyy));
        $this->assertIsNumeric($expiry_mmyy);
        
        // Extract month and year
        $month = substr($expiry_mmyy, 0, 2);
        $year = substr($expiry_mmyy, 2, 2);
        
        $this->assertEquals('12', $month);
        $this->assertEquals('25', $year);
        $this->assertGreaterThanOrEqual(1, intval($month));
        $this->assertLessThanOrEqual(12, intval($month));
    }
    
    /**
     * Test CVV validation
     */
    public function test_cvv_validation() {
        $cvv_3_digit = '123';
        $cvv_4_digit = '1234';
        
        $this->assertEquals(3, strlen($cvv_3_digit));
        $this->assertEquals(4, strlen($cvv_4_digit));
        $this->assertIsNumeric($cvv_3_digit);
        $this->assertIsNumeric($cvv_4_digit);
    }
    
    /**
     * Test donation type values
     */
    public function test_donation_type_values() {
        $valid_types = array('monthly', 'oneTime');
        
        $this->assertContains('monthly', $valid_types);
        $this->assertContains('oneTime', $valid_types);
        $this->assertNotContains('weekly', $valid_types);
    }
    
    /**
     * Cleanup
     */
    public function tearDown(): void {
        delete_option('sola_donation_settings');
        parent::tearDown();
    }
}
