<?php
/**
 * Webhook Handler Tests
 */

require_once dirname(dirname(__DIR__)) . '/includes/webhook-handler.php';

class Test_Webhook_Handler extends WP_UnitTestCase {

    /**
     * Test webhook payload structure
     */
    public function test_webhook_payload_structure() {
        $form_data = array(
            'donationType' => 'oneTime',
            'amount' => 100,
            'currency' => 'USD',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St'
        );
        
        $transaction_data = array(
            'refNum' => 'ABC123',
            'authAmount' => '100.00',
            'authCode' => 'XYZ789',
            'maskedCard' => 'XXXXXXXXXXXX1111',
            'cardType' => 'Visa'
        );
        
        $payload = array(
            'event' => 'donation_successful',
            'timestamp' => current_time('mysql'),
            'donation' => array(
                'type' => $form_data['donationType'],
                'amount' => $form_data['amount'],
                'currency' => $form_data['currency']
            ),
            'donor' => array(
                'firstName' => $form_data['firstName'],
                'lastName' => $form_data['lastName'],
                'email' => $form_data['email'],
                'phone' => $form_data['phone'],
                'address' => $form_data['address']
            ),
            'transaction' => $transaction_data
        );
        
        // Test payload structure
        $this->assertArrayHasKey('event', $payload);
        $this->assertArrayHasKey('timestamp', $payload);
        $this->assertArrayHasKey('donation', $payload);
        $this->assertArrayHasKey('donor', $payload);
        $this->assertArrayHasKey('transaction', $payload);
        
        // Test donation data
        $this->assertEquals('oneTime', $payload['donation']['type']);
        $this->assertEquals(100, $payload['donation']['amount']);
        $this->assertEquals('USD', $payload['donation']['currency']);
        
        // Test donor data
        $this->assertEquals('John', $payload['donor']['firstName']);
        $this->assertEquals('Doe', $payload['donor']['lastName']);
        $this->assertEquals('john@example.com', $payload['donor']['email']);
        
        // Test transaction data
        $this->assertEquals('ABC123', $payload['transaction']['refNum']);
        $this->assertEquals('Visa', $payload['transaction']['cardType']);
    }
    
    /**
     * Test recurring donation payload
     */
    public function test_recurring_donation_payload() {
        $form_data = array(
            'donationType' => 'monthly',
            'amount' => 50,
            'currency' => 'ILS',
            'chargeDay' => 15,
            'chargeNow' => true,
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '098-765-4321',
            'address' => '456 Oak Ave'
        );
        
        $payload = array(
            'event' => 'donation_successful',
            'donation' => array(
                'type' => $form_data['donationType'],
                'amount' => $form_data['amount'],
                'currency' => $form_data['currency'],
                'recurring' => array(
                    'chargeDay' => $form_data['chargeDay'],
                    'chargeNow' => $form_data['chargeNow']
                )
            )
        );
        
        // Test recurring data
        $this->assertEquals('monthly', $payload['donation']['type']);
        $this->assertArrayHasKey('recurring', $payload['donation']);
        $this->assertEquals(15, $payload['donation']['recurring']['chargeDay']);
        $this->assertTrue($payload['donation']['recurring']['chargeNow']);
    }
    
    /**
     * Test signature generation
     */
    public function test_signature_generation() {
        $payload = array('test' => 'data');
        
        $signature1 = sola_donation_generate_signature($payload);
        $signature2 = sola_donation_generate_signature($payload);
        
        // Same payload should generate same signature
        $this->assertEquals($signature1, $signature2);
        
        // Signature should be a hash
        $this->assertEquals(64, strlen($signature1)); // SHA256 = 64 chars
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature1);
    }
    
    /**
     * Test URL validation
     */
    public function test_webhook_url_validation() {
        $valid_url = 'https://example.com/webhook';
        $invalid_url = 'not-a-url';
        
        $this->assertNotFalse(filter_var($valid_url, FILTER_VALIDATE_URL));
        $this->assertFalse(filter_var($invalid_url, FILTER_VALIDATE_URL));
    }
    
    /**
     * Test empty webhook URL handling
     */
    public function test_empty_webhook_url() {
        $result = sola_donation_send_webhook('', array(), array());
        $this->assertFalse($result, 'Should return false for empty URL');
    }
}
