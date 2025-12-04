<?php
/**
 * Donation Form Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load form settings from database
$settings = get_option('sola_donation_settings');
$form_settings = isset($settings['form_settings']) ? $settings['form_settings'] : array();

// Get preset amounts
$preset_amounts = isset($form_settings['preset_amounts']) ? $form_settings['preset_amounts'] : array(
    'USD' => array(10, 25, 50, 100),
    'CAD' => array(10, 25, 50, 100),
    'EUR' => array(10, 25, 50, 100),
    'GBP' => array(10, 25, 50, 100)
);

// Get enabled currencies
$enabled_currencies = isset($form_settings['enabled_currencies']) ? $form_settings['enabled_currencies'] : array('USD', 'CAD', 'EUR', 'GBP');

// Get default currency
$default_currency = isset($form_settings['default_currency']) ? $form_settings['default_currency'] : 'USD';

// Get required fields
$required_fields = isset($form_settings['required_fields']) ? $form_settings['required_fields'] : array(
    'firstName' => true,
    'lastName' => true,
    'phone' => true,
    'email' => true,
    'address' => true,
    'taxId' => false
);

// Currency symbols mapping
$currency_symbols = array(
    'USD' => 'US$',
    'CAD' => 'CA$',
    'EUR' => '€',
    'GBP' => '£'
);
?>

<div class="sola-donation-form-wrapper" id="solaDonationForm" data-lang="he" dir="rtl">
    <div class="sola-form-container">
        <!-- Header -->
        <div class="sola-form-header">
            <div class="sola-header-content">
                <h1 class="sola-title" data-he="תרומה לאור מאיר וברכה" data-en="Donate to Ohr Meir & Bracha">תרומה לאור מאיר וברכה</h1>
                <p class="sola-subtitle" data-he="כל תרומה תתקבל באהבה" data-en="Any donation will be gratefully received">כל תרומה תתקבל באהבה</p>
            </div>
            <button type="button" class="sola-lang-toggle" id="langToggle">
                <span data-he="English" data-en="עברית">English</span>
            </button>
        </div>
        
        <!-- Progress Indicator -->
        <div class="sola-progress-steps">
            <div class="sola-step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label" data-he="פרטים אישיים" data-en="Personal Info">פרטים אישיים</div>
            </div>
            <div class="sola-step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label" data-he="פרטי תרומה" data-en="Donation Details">פרטי תרומה</div>
            </div>
            <div class="sola-step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label" data-he="תשלום" data-en="Payment">תשלום</div>
            </div>
        </div>

        <form id="solaDonationFormElement" class="sola-donation-form">
            <!-- Personal Details Section -->
            <div class="sola-form-section" data-step="1">
                <h2 class="sola-section-title">
                    <div class="sola-section-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <span data-he="פרטים אישיים" data-en="Personal Details">פרטים אישיים</span>
                </h2>
                
                <div class="sola-form-row">
                    <div class="sola-form-field">
                        <div class="sola-input-wrapper">
                            <input type="text" name="firstName" id="firstName" class="sola-input" <?php echo $required_fields['firstName'] ? 'required' : ''; ?> placeholder=" ">
                            <label class="sola-label" data-he="שם פרטי" data-en="First Name">שם פרטי</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="sola-form-field">
                        <div class="sola-input-wrapper">
                            <input type="text" name="lastName" id="lastName" class="sola-input" <?php echo $required_fields['lastName'] ? 'required' : ''; ?> placeholder=" ">
                            <label class="sola-label" data-he="שם משפחה" data-en="Last Name">שם משפחה</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M9 21v-2a4 4 0 0 1 3-3.87"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <circle cx="15" cy="7" r="4"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="sola-form-row">
                    <div class="sola-form-field">
                        <div class="sola-input-wrapper">
                            <input type="tel" name="phone" id="phone" class="sola-input" <?php echo $required_fields['phone'] ? 'required' : ''; ?> placeholder=" ">
                            <label class="sola-label" data-he="טלפון" data-en="Phone">טלפון</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="sola-form-field">
                        <div class="sola-input-wrapper">
                            <input type="email" name="email" id="email" class="sola-input" <?php echo $required_fields['email'] ? 'required' : ''; ?> placeholder=" ">
                            <label class="sola-label" data-he="דואר אלקטרוני" data-en="Email">דואר אלקטרוני</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </span>
                            <span class="sola-validation-icon"></span>
                        </div>
                    </div>
                </div>
                
                <div class="sola-form-row">
                    <div class="sola-form-field">
                        <div class="sola-input-wrapper">
                            <input type="text" name="address" id="address" class="sola-input" <?php echo $required_fields['address'] ? 'required' : ''; ?> placeholder=" ">
                            <label class="sola-label" data-he="כתובת מגורים" data-en="Address">כתובת מגורים</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="sola-form-field">
                        <div class="sola-input-wrapper">
                            <input type="text" name="taxId" id="taxId" class="sola-input" <?php echo $required_fields['taxId'] ? 'required' : ''; ?> placeholder=" ">
                            <label class="sola-label" data-he="מספר עוסק / ת.ז" data-en="Tax ID">מספר עוסק / ת.ז</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <circle cx="8" cy="10" r="2"></circle>
                                    <path d="M14 8h4"></path>
                                    <path d="M14 12h4"></path>
                                    <path d="M6 16h12"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donation Details Section -->
            <div class="sola-form-section" data-step="2">
                <h2 class="sola-section-title">
                    <div class="sola-section-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </div>
                    <span data-he="פרטי תרומה" data-en="Donation Details">פרטי תרומה</span>
                </h2>
                
                <div class="sola-form-row">
                    <div class="sola-form-field">
                        <label class="sola-field-label" data-he="מטבע" data-en="Currency">מטבע</label>
                        <div class="sola-button-group" id="currencyGroup">
                            <?php
                            // Always render all 4 currency buttons
                            // JavaScript will show/hide based on enabled_currencies
                            $all_currencies = array(
                                'USD' => 'US$',
                                'CAD' => 'CA$',
                                'EUR' => '€',
                                'GBP' => '£'
                            );
                            
                            foreach ($all_currencies as $currency => $symbol):
                                $is_enabled = in_array($currency, $enabled_currencies);
                                $is_active = ($currency === $default_currency && $is_enabled) ? 'active' : '';
                                $style = !$is_enabled ? ' style="display:none;"' : '';
                            ?>
                                <button type="button" class="sola-option-btn <?php echo $is_active; ?>" data-value="<?php echo esc_attr($currency); ?>"<?php echo $style; ?>>
                                    <?php echo esc_html($symbol); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="currency" id="currency" value="<?php echo esc_attr($default_currency); ?>">
                    </div>
                    
                    <div class="sola-form-field">
                        <label class="sola-field-label" data-he="סוג תרומה" data-en="Donation Type">סוג תרומה</label>
                        <div class="sola-button-group" id="donationTypeGroup">
                            <button type="button" class="sola-option-btn active" data-value="monthly" data-he="חודשי" data-en="Monthly">חודשי</button>
                            <button type="button" class="sola-option-btn" data-value="oneTime" data-he="חד פעמי" data-en="One-Time">חד פעמי</button>
                        </div>
                        <input type="hidden" name="donationType" id="donationType" value="monthly">
                    </div>
                </div>
                
                <div class="sola-form-row">
                    <div class="sola-form-field sola-full-width">
                        <label class="sola-field-label" data-he="סכום התרומה" data-en="Donation Amount">סכום התרומה</label>
                        <div class="sola-amount-grid" id="amountGrid">
                            <?php 
                            // Get amounts for default currency
                            $default_amounts = isset($preset_amounts[$default_currency]) ? $preset_amounts[$default_currency] : array(10, 25, 50, 100);
                            $default_symbol = isset($currency_symbols[$default_currency]) ? $currency_symbols[$default_currency] : '$';
                            $first_amount = true;
                            
                            foreach ($default_amounts as $index => $amount):
                                $is_active = ($first_amount && $index === 0) ? '' : ($index === 2 ? 'active' : '');
                                if ($index === 0) $first_amount = false;
                            ?>
                                <button type="button" class="sola-amount-btn <?php echo $is_active; ?>" data-value="<?php echo esc_attr($amount); ?>">
                                    <span class="amount-symbol"><?php echo esc_html($default_symbol); ?></span><?php echo esc_html($amount); ?>
                                </button>
                            <?php endforeach; ?>
                            
                            <button type="button" class="sola-amount-btn sola-custom-amount-btn" id="customAmountBtn">
                            <span class="custom-label" data-he="סכום אחר" data-en="Custom Amount">סכום אחר</span>
                            <span class="currency-symbol" style="display: none;"><?php echo esc_html($default_symbol); ?></span>
                            <input type="number" 
                                   class="custom-amount-input number-font" 
                                   id="customAmountInput" 
                                   min="1" 
                                   step="0.01" 
                                   placeholder="0"
                                   style="display: none;">
                            <svg class="edit-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        </div>
                        <?php 
                        // Get the third amount (index 2) or first if less than 3
                        $default_amount_value = isset($default_amounts[2]) ? $default_amounts[2] : (isset($default_amounts[0]) ? $default_amounts[0] : 50);
                        ?>
                        <input type="hidden" name="amount" id="amount" value="<?php echo esc_attr($default_amount_value); ?>">
                    </div>
                </div>
                
                <!-- Charge Day Selection and First Charge Checkbox (Monthly Only) -->
                <div class="sola-form-row" id="chargeMonthlyRow">
                    <div class="sola-form-field">
                        <label class="sola-field-label" data-he="בחירת יום לחיוב" data-en="Select Charge Day">בחירת יום לחיוב</label>
                        <div class="sola-input-wrapper">
                            <select name="chargeDay" id="chargeDay" class="sola-input">
                                <?php for ($i = 1; $i <= 28; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <label class="sola-label" data-he="יום לחיוב" data-en="Charge Day">יום לחיוב</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="sola-form-field sola-checkbox-field">
                        <label class="sola-field-label" style="opacity: 0; pointer-events: none;">‎</label>
                        <div class="sola-checkbox-wrapper">
                            <input type="checkbox" name="chargeNow" id="chargeNow" class="sola-checkbox" checked>
                            <div class="sola-checkbox-custom"></div>
                            <span class="sola-checkbox-text" data-he="לחייב את התרומה הראשונה מהחודש הנוכחי" data-en="Charge first donation from current month">לחייב את התרומה הראשונה מהחודש הנוכחי</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details Section -->
            <div class="sola-form-section" data-step="3">
                <div class="sola-section-header">
                    <h2 class="sola-section-title">
                        <div class="sola-section-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                <line x1="1" y1="10" x2="23" y2="10"></line>
                            </svg>
                        </div>
                        <span data-he="פרטי תשלום" data-en="Payment Details">פרטי תשלום</span>
                    </h2>
                    <div class="sola-security-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span data-he="תשלום מאובטח" data-en="Secure Payment">תשלום מאובטח</span>
                    </div>
                </div>
                
                <div class="sola-form-row">
                    <div class="sola-form-field sola-full-width">
                        <div class="sola-input-wrapper">
                            <input type="text" name="cardNumber" id="cardNumber" class="sola-input number-font" maxlength="19" required autocomplete="cc-number" placeholder=" ">
                            <label class="sola-label" data-he="מספר כרטיס אשראי" data-en="Credit Card Number">מספר כרטיס אשראי</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="cardIcon">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                            </span>
                            <span class="card-type-logo" id="cardTypeLogo"></span>
                        </div>
                    </div>
                </div>
                
                <div class="sola-form-row">
                    <div class="sola-form-field">
                        <div class="sola-input-wrapper">
                            <input type="text" name="expiry" id="expiry" class="sola-input number-font" placeholder=" " maxlength="5" required autocomplete="cc-exp">
                            <label class="sola-label" data-he="תוקף" data-en="Expiry">תוקף</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="sola-form-field">
                        <div class="sola-input-wrapper">
                            <input type="text" name="cvv" id="cvv" class="sola-input number-font" maxlength="4" required autocomplete="cc-csc" placeholder=" ">
                            <label class="sola-label" data-he="CVV" data-en="CVV">CVV</label>
                            <span class="sola-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="sola-encryption-notice">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span data-he="מוצפן 256-bit" data-en="256-bit Encrypted">מוצפן 256-bit</span>
                </div>
                
                <!-- Payment Methods -->
                <div class="sola-payment-methods">
                    <div class="sola-payment-methods-title" data-he="או תשלום מהיר" data-en="Or pay with">או תשלום מהיר</div>
                    <div class="sola-wallet-buttons">
                        <button type="button" class="sola-wallet-btn google-pay" title="Requires merchant verification">
                            <svg class="google-pay-logo" width="120" height="48" viewBox="0 -209.5 512 512" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid">
                                <g>
                                    <path d="M482.684036,37.1058143 L493.629766,63.5409278 L493.758919,63.5409278 L504.446342,37.1058143 L511.999495,37.1058143 L488.957189,90.1681566 L481.789189,90.1681566 L490.366342,71.606081 L475.195459,37.1058143 L482.684036,37.1058143 Z M457.84973,35.889699 C462.96973,35.889699 467.001153,37.2338143 469.946306,39.9861602 C472.891459,42.7385062 474.362883,46.4509675 474.362883,51.251544 L474.362883,73.9741963 L467.769153,73.9741963 L467.769153,68.8535044 L467.450883,68.8535044 C464.570306,73.0781963 460.792576,75.1903116 456.055423,75.1903116 C452.024,75.1903116 448.631423,73.9741963 445.88,71.6058503 C443.12627,69.1736197 441.781694,66.1652738 441.781694,62.5808125 C441.781694,58.8044666 443.190847,55.7320053 446.136,53.4916593 C449.016576,51.251544 452.856576,50.0993134 457.656,50.0993134 C461.752,50.0993134 465.144576,50.867544 467.83373,52.3396593 L467.83373,50.7393134 C467.83373,48.3070828 466.874306,46.3229675 464.953153,44.5946215 C463.034306,42.9303909 460.792576,42.0982756 458.232576,42.0982756 C454.392576,42.0982756 451.320576,43.6986215 449.078847,46.9629675 L442.999423,43.1225062 C446.327423,38.3219296 451.320576,35.889699 457.84973,35.889699 Z M422.899964,20.0799462 C427.57254,20.0799462 431.541694,21.6161307 434.805117,24.7525458 C438.133117,27.8888918 439.733694,31.6652377 439.733694,36.0818143 C439.733694,40.6263909 438.06854,44.4668521 434.805117,47.4751981 C431.603964,50.5476593 427.637117,52.08389 422.899964,52.08389 L411.506811,52.08389 L411.506811,73.9103116 L404.592504,73.9103116 L404.592504,20.0799462 L422.899964,20.0799462 Z M458.744576,55.53989 C455.928576,55.53989 453.624576,56.2440053 451.703423,57.5881206 C449.846847,58.9963512 448.887423,60.6605819 448.887423,62.6449278 C448.887423,64.4371584 449.655423,65.9731584 451.191423,67.1253891 C452.727423,68.3415044 454.519423,68.9176197 456.567423,68.9176197 C459.448,68.9176197 462.072576,67.8295044 464.376576,65.7171584 C466.680576,63.5409278 467.769153,61.0445819 467.769153,58.1003512 C465.594306,56.3720053 462.584576,55.53989 458.744576,55.53989 Z M423.091387,26.8007765 L411.506811,26.8007765 L411.506811,45.5549675 L423.091387,45.5549675 C425.845117,45.5549675 428.149117,44.6589675 429.941117,42.8026215 C431.797694,40.9465062 432.69254,38.7702756 432.69254,36.2098143 C432.69254,33.713699 431.797694,31.5372377 429.941117,29.6811224 C428.149117,27.7608918 425.845117,26.8007765 423.091387,26.8007765 Z" fill="#5F6368"/>
                                    <path d="M306.91582,35.9538143 C311.588396,35.9538143 315.301549,38.0020449 317.222703,40.3062756 L317.540973,40.3062756 L317.540973,37.1060449 L325.735279,37.1060449 L325.735279,72.3101963 C325.735279,86.7758107 317.222703,92.7286179 307.109549,92.7286179 C297.635243,92.7286179 291.938666,86.327926 289.761513,81.1433494 L297.252396,78.0067729 C298.594666,81.2072341 301.860396,84.9838107 307.109549,84.9838107 C313.574126,84.9838107 317.540973,80.9512341 317.540973,73.4624269 L317.540973,70.6459657 L317.222703,70.6459657 C315.301549,73.0143116 311.588396,75.1264269 306.91582,75.1264269 C297.123243,75.1264269 288.163243,66.6133891 288.163243,55.6042359 C288.163243,44.5309675 297.123243,35.9538143 306.91582,35.9538143 Z M363.245045,36.0179296 C373.422775,36.0179296 378.351351,44.0830828 380.014198,48.4994287 L380.911351,50.7397747 L354.732468,61.5570431 C356.715892,65.4616197 359.852468,67.5098503 364.204468,67.5098503 C368.558775,67.3818503 371.630775,65.2695044 373.870198,62.0051584 L380.526198,66.4856197 C378.351351,69.686081 373.166775,75.1907729 364.204468,75.1907729 C353.067315,75.1907729 344.810739,66.6136197 344.810739,55.6044666 C344.810739,43.9548521 353.196468,36.0179296 363.245045,36.0179296 Z M171.608519,14.3198078 C180.889557,14.3198078 187.482364,17.968246 192.41094,22.7047765 L186.522248,28.5934684 C182.937787,25.2652377 178.137211,22.6408918 171.544403,22.6408918 C159.318904,22.6408918 149.717751,32.4980449 149.717751,44.723544 C149.717751,56.9488125 159.25502,66.8061963 171.544403,66.8061963 C179.481326,66.8061963 184.025903,63.605735 186.906248,60.7253891 C189.274594,58.3570431 190.810825,54.9646972 191.450825,50.2921206 L171.352519,50.2921206 L171.352519,41.9711981 L199.515748,41.9711981 C199.835863,43.4433134 199.963863,45.235544 199.963863,47.1557747 C199.963863,53.3645819 198.235517,61.1093891 192.79494,66.614081 C187.418248,72.1826575 180.633557,75.1271188 171.608519,75.1271188 C154.838443,75.1271188 140.756829,61.4933891 140.756829,44.723544 C140.756829,27.9534684 154.838443,14.3198078 171.608519,14.3198078 Z M222.497167,35.9538143 C233.314666,35.9538143 242.14782,44.2108521 242.14782,55.5401206 C242.14782,66.8055044 233.314666,75.1264269 222.497167,75.1264269 C211.679899,75.1264269 202.846746,66.8055044 202.846746,55.5401206 C202.846746,44.2108521 211.679899,35.9538143 222.497167,35.9538143 Z M265.252396,35.9538143 C276.068973,35.9538143 284.902126,44.2108521 284.902126,55.5401206 C284.902126,66.8055044 276.068973,75.1264269 265.252396,75.1264269 C254.433513,75.1264269 245.60036,66.8055044 245.60036,55.5401206 C245.60036,44.2108521 254.433513,35.9538143 265.252396,35.9538143 Z M340.523315,16.368246 L340.523315,73.9112341 L331.946162,73.9112341 L331.946162,16.368246 L340.523315,16.368246 Z M222.561283,43.6988521 C216.672591,43.6988521 211.551899,48.4994287 211.551899,55.5401206 C211.551899,62.5169278 216.672591,67.3816197 222.561283,67.3816197 C228.449975,67.3816197 233.570666,62.5169278 233.570666,55.5401206 C233.570666,48.4994287 228.449975,43.6988521 222.561283,43.6988521 Z M265.252396,43.6988521 C259.36209,43.6988521 254.24209,48.4994287 254.24209,55.5401206 C254.24209,62.5169278 259.36209,67.3816197 265.252396,67.3816197 C271.140396,67.3816197 276.260396,62.5169278 276.260396,55.5401206 C276.260396,48.4994287 271.140396,43.6988521 265.252396,43.6988521 Z M307.748396,43.6347368 C301.79582,43.6347368 296.867243,48.6913134 296.867243,55.6042359 C296.867243,62.4530431 301.860396,67.3816197 307.748396,67.3816197 C313.574126,67.3816197 318.182126,62.4530431 318.182126,55.6042359 C318.182126,48.6913134 313.574126,43.6347368 307.748396,43.6347368 Z M363.501045,43.5069675 C359.149045,43.5069675 353.067315,47.4113134 353.323315,54.9642359 L370.798198,47.6673134 C369.838775,45.2350828 366.958198,43.5069675 363.501045,43.5069675 Z" fill="#5F6368"/>
                                    <path d="M95.3629548,17.2958655 C85.0064863,11.3175505 71.7634449,14.8699772 65.77858,25.2264918 L50.6921079,51.3606323 C46.3267313,58.9073278 51.9465079,61.5443584 58.2129728,65.3015621 L72.7297872,73.6801422 C77.6456791,76.5157458 83.9248286,74.8323729 86.7604322,69.9229386 L102.26942,43.0653098 C107.479596,34.040272 104.387993,22.5062035 95.3629548,17.2958655 Z" fill="#EA4335"/>
                                    <path d="M78.2923674,28.1128341 L63.775553,19.7343231 C55.7618304,15.2858042 51.2237115,14.9913581 47.9335349,20.2207923 L26.523171,57.3005242 C20.5447638,67.650535 24.103625,80.8873494 34.4536358,86.8528413 C43.4786737,92.0630179 55.0129728,88.9714143 60.2231494,79.9463765 L82.043344,42.1497062 C84.8916322,37.2340449 83.2082593,30.9484377 78.2923674,28.1128341 Z" fill="#FBBC04"/>
                                    <path d="M81.0864575,9.05160457 L70.8900467,3.16291268 C59.6119782,-3.34667377 45.1911061,0.512985186 38.6815566,11.7911275 L19.2679695,45.4142828 C16.4004236,50.374917 18.1030312,56.7244089 23.0637115,59.5856125 L34.4826953,66.1784197 C40.1216142,69.4365386 47.3288214,67.5033927 50.5869403,61.8642431 L72.7655349,23.4534035 C77.3613115,15.4971772 87.5321223,12.7704543 95.4881872,17.3662078 L81.0864575,9.05160457 Z" fill="#34A853"/>
                                    <path d="M41.4394376,21.4111923 L30.4173692,15.0616312 C25.5014773,12.2325084 19.22242,13.9094929 16.3868856,18.8124925 L3.16291728,41.6633062 C-3.34667839,52.909317 0.512987482,67.2983621 11.7911321,73.7887693 L20.1825353,78.6211729 L30.3597115,84.4842648 L34.7762881,87.025353 C26.9353079,81.7768918 24.4454196,71.2603657 29.2395385,62.9777278 L32.6639421,57.0634359 L45.2030989,35.3968413 C48.0322449,30.5067801 46.3488719,24.2403152 41.4394376,21.4111923 Z" fill="#4285F4"/>
                                </g>
                            </svg>
                        </button>
                        <button type="button" class="sola-wallet-btn apple-pay" title="Requires merchant verification">
                            <svg class="apple-pay-logo" width="120" height="48" viewBox="0 -150.5 512 512" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid">
                                <g>
                                    <path d="M93.5520633,27.1031049 C87.5513758,34.2039183 77.9502759,39.8045599 68.349176,39.0044683 C67.1490386,29.4033684 71.849577,19.2021998 77.3502072,12.901478 C83.3508946,5.6006416 93.8520976,0.400045829 102.353071,0 C103.353186,10.0011457 99.4527392,19.8022685 93.5520633,27.1031049 Z M102.25306,40.904686 C88.3514675,40.1045943 76.4501041,48.8055911 69.8493479,48.8055911 C63.1485803,48.8055911 53.0474231,41.3047318 42.0461628,41.5047547 C27.7445244,41.7047776 14.4430006,49.8057057 7.14216427,62.7071836 C-7.8595543,88.5101396 3.24171744,126.714516 17.7433787,147.716922 C24.8441922,158.118114 33.345166,169.51942 44.5464492,169.119374 C55.1476637,168.719328 59.3481449,162.218584 72.1496114,162.218584 C85.0510894,162.218584 88.7515133,169.119374 99.9527965,168.919351 C111.554126,168.719328 118.854962,158.51816 125.955775,148.116968 C134.056703,136.315616 137.357081,124.814299 137.557104,124.21423 C137.357081,124.014207 115.154538,115.513233 114.954515,89.9103 C114.754492,68.5078482 132.45652,58.3066795 133.256612,57.7066108 C123.255466,42.9049151 107.653679,41.3047318 102.25306,40.904686 Z M182.56226,11.9013634 L182.56226,167.819225 L206.765033,167.819225 L206.765033,114.513118 L240.268871,114.513118 C270.872377,114.513118 292.37484,93.5107124 292.37484,63.1072295 C292.37484,32.7037465 271.272423,11.9013634 241.068963,11.9013634 L182.56226,11.9013634 Z M206.765033,32.3037007 L234.668229,32.3037007 C255.670635,32.3037007 267.67201,43.5049839 267.67201,63.2072409 C267.67201,82.909498 255.670635,94.2107926 234.568218,94.2107926 L206.765033,94.2107926 L206.765033,32.3037007 Z M336.579904,169.019363 C351.781646,169.019363 365.883261,161.31848 372.283994,149.117083 L372.784052,149.117083 L372.784052,167.819225 L395.186618,167.819225 L395.186618,90.2103344 C395.186618,67.7077565 377.184556,53.2060952 349.481382,53.2060952 C323.778438,53.2060952 304.776261,67.9077794 304.076181,88.1100938 L325.878678,88.1100938 C327.678884,78.5089939 336.579904,72.2082721 348.781302,72.2082721 C363.582998,72.2082721 371.883949,79.1090626 371.883949,91.8105177 L371.883949,100.411503 L341.680488,102.211709 C313.577269,103.911904 298.375528,115.413222 298.375528,135.415513 C298.375528,155.617827 314.077326,169.019363 336.579904,169.019363 Z M343.080649,150.517243 C330.179171,150.517243 321.978231,144.316533 321.978231,134.815444 C321.978231,125.014321 329.879137,119.313668 344.980867,118.413565 L371.883949,116.713371 L371.883949,125.514379 C371.883949,140.116051 359.482528,150.517243 343.080649,150.517243 Z M425.090044,210.224083 C448.692748,210.224083 459.794019,201.223052 469.495131,173.919924 L512,54.7062671 L487.397182,54.7062671 L458.893916,146.816819 L458.393859,146.816819 L429.890594,54.7062671 L404.587695,54.7062671 L445.592392,168.219271 L443.39214,175.120061 C439.691716,186.821402 433.691029,191.321918 422.989803,191.321918 C421.089585,191.321918 417.389162,191.121895 415.88899,190.921872 L415.88899,209.624014 C417.28915,210.02406 423.289838,210.224083 425.090044,210.224083 Z" fill="white"/>
                                </g>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Navigation for Step 3 with Submit Button -->
                <div class="sola-form-navigation">
                    <button type="button" class="sola-nav-btn btn-back" onclick="window.solaDonationGoToStep(2)">
                        <span class="nav-text" data-he="חזור לפרטי תרומה" data-en="Back to Donation Details">חזור לפרטי תרומה</span>
                    </button>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="sola-submit-btn" id="submitBtn">
                        <svg class="heart-icon" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span class="btn-text">
                            <span data-he="תרום" data-en="Donate">תרום</span>
                            <span class="amount-display number-font"><?php echo esc_html($default_symbol . $default_amount_value); ?></span>
                            <span data-he="עכשיו" data-en="Now">עכשיו</span>
                        </span>
                        <span class="spinner" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="spin">
                                <line x1="12" y1="2" x2="12" y2="6"></line>
                                <line x1="12" y1="18" x2="12" y2="22"></line>
                                <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                <line x1="2" y1="12" x2="6" y2="12"></line>
                                <line x1="18" y1="12" x2="22" y2="12"></line>
                                <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>

            
            <!-- Messages -->
            <div class="sola-message" id="formMessage" style="display: none;"></div>
        </form>
    </div>
</div>