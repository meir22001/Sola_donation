/**
 * Sola Donation Form JavaScript - Enhanced Multi-Step Version
 */

(function ($) {
    'use strict';

    // State
    let currentLang = 'he';
    let currentCurrency = 'ILS';
    let currentAmount = '100';
    let donationType = 'monthly';
    let currentStep = 1;

    // Currency symbols
    const currencySymbols = {
        'ILS': '₪',
        'USD': '$',
        'EUR': '€'
    };

    // Card type patterns
    const cardPatterns = {
        visa: /^4/,
        mastercard: /^(5[1-5]|2[2-7])/,
        amex: /^3[47]/,
        discover: /^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5])|64[4-9]|65)/,
        diners: /^(30[0-5]|36|38)/,
        jcb: /^35/
    };

    // Card logos (SVG file paths)
    const cardLogos = {
        visa: solaDonation.pluginUrl + 'public/icons/visa.svg',
        mastercard: solaDonation.pluginUrl + 'public/icons/mastercard.svg',
        amex: solaDonation.pluginUrl + 'public/icons/amex.svg',
        discover: solaDonation.pluginUrl + 'public/icons/discover.svg',
        diners: solaDonation.pluginUrl + 'public/icons/diners.svg',
        jcb: solaDonation.pluginUrl + 'public/icons/jcb.svg'
    };

    // Initialize when DOM is ready
    $(document).ready(function () {
        initMultiStep();
        initLanguageToggle();
        initCurrencySelection();
        initDonationType();
        initAmountSelection();
        initCardValidation();
        initFormSubmission();
        initPaymentMethods();
    });

    /**
     * Multi-Step Navigation
     */
    function initMultiStep() {
        // Show first step by default
        $('.sola-form-section').removeClass('active');
        $('.sola-form-section[data-step="1"]').addClass('active');

        // Remove existing navigation from steps 1 and 2 only (keep step 3 with submit button)
        $('.sola-form-section[data-step="1"] .sola-form-navigation').remove();
        $('.sola-form-section[data-step="2"] .sola-form-navigation').remove();

        // Add navigation to step 1 (only next)
        $('.sola-form-section[data-step="1"]').append(createNavigation(1));

        // Add navigation to step 2 (back + next)
        $('.sola-form-section[data-step="2"]').append(createNavigation(2));

        // Step 3 has submit button in HTML, no navigation needed

        // Navigation handlers
        $(document).on('click', '.btn-next', function () {
            if (validateStep(currentStep)) {
                goToStep(currentStep + 1);
            }
        });

        $(document).on('click', '.btn-back', function () {
            goToStep(currentStep - 1);
        });

        // Step indicator click
        $('.sola-step').on('click', function () {
            const stepNum = parseInt($(this).data('step'));
            if (stepNum < currentStep || validateStep(currentStep)) {
                goToStep(stepNum);
            }
        });

        // Update button texts on language change
        updateNavigationTexts();
    }

    function createNavigation(step) {
        let html = '<div class="sola-form-navigation">';

        if (step === 1) {
            // Only next button
            html += `
                <button type="button" class="sola-nav-btn btn-next">
                    <span class="nav-text" data-he="המשך אל פרטי תרומה" data-en="Continue to Donation Details">המשך אל פרטי תרומה</span>
                </button>
            `;
        } else if (step === 2) {
            // Back and next buttons
            html += `
                <button type="button" class="sola-nav-btn btn-back">
                    <span class="nav-text" data-he="חזור אל פרטים אישיים" data-en="Back to Personal Info">חזור אל פרטים אישיים</span>
                </button>
                <button type="button" class="sola-nav-btn btn-next">
                    <span class="nav-text" data-he="המשך לתשלום" data-en="Continue to Payment">המשך לתשלום</span>
                </button>
            `;
        }

        html += '</div>';
        return html;
    }

    function updateNavigationTexts() {
        const lang = currentLang;
        $('.nav-text[data-he][data-en]').each(function () {
            $(this).text($(this).data(lang));
        });
    }

    function goToStep(stepNum) {
        if (stepNum < 1 || stepNum > 3) return;

        // Clear any error messages and states when changing steps
        $('#formMessage').slideUp(300);
        $('.sola-input').removeClass('error');

        // Hide all steps
        $('.sola-form-section').removeClass('active');

        // Show target step
        $(`.sola-form-section[data-step="${stepNum}"]`).addClass('active');

        // Update progress indicator
        $('.sola-step').removeClass('active completed');
        $(`.sola-step[data-step="${stepNum}"]`).addClass('active');

        // Mark previous steps as completed
        for (let i = 1; i < stepNum; i++) {
            $(`.sola-step[data-step="${i}"]`).addClass('completed');
        }

        currentStep = stepNum;

        // Scroll to top
        $('html, body').animate({
            scrollTop: $('.sola-form-header').offset().top - 20
        }, 300);
    }

    // Expose goToStep globally for onclick handlers
    window.solaDonationGoToStep = goToStep;

    function validateStep(step) {
        let isValid = true;
        const $section = $(`.sola-form-section[data-step="${step}"]`);

        // Validation messages
        const messages = {
            he: {
                fillRequired: 'אנא מלא את כל השדות הנדרשים',
                invalidEmail: 'כתובת אימייל לא תקינה',
                selectAmount: 'אנא בחר סכום תרומה',
                invalidCard: 'מספר כרטיס לא תקין',
                invalidExpiry: 'תוקף לא תקין',
                invalidCVV: 'CVV לא תקין'
            },
            en: {
                fillRequired: 'Please fill all required fields',
                invalidEmail: 'Invalid email address',
                selectAmount: 'Please select a donation amount',
                invalidCard: 'Invalid card number',
                invalidExpiry: 'Invalid expiry date',
                invalidCVV: 'Invalid CVV'
            }
        };

        const msg = messages[currentLang];

        // Validate required fields in current step
        $section.find('input[required]').each(function () {
            if (!$(this).val() || $(this).val().trim() === '') {
                isValid = false;
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });

        if (!isValid) {
            showMessage('error', msg.fillRequired);
        }

        return isValid;
    }

    /**
     * Language Toggle
     */
    function initLanguageToggle() {
        $('#langToggle').on('click', function () {
            currentLang = currentLang === 'he' ? 'en' : 'he';
            switchLanguage(currentLang);
        });
    }

    function switchLanguage(lang) {
        const $wrapper = $('#solaDonationForm');
        const dir = lang === 'he' ? 'rtl' : 'ltr';

        $wrapper.attr('data-lang', lang).attr('dir', dir);

        // Update all text elements
        $('[data-he][data-en]').each(function () {
            const $el = $(this);
            $el.text($el.data(lang));
        });

        // Update navigation texts
        updateNavigationTexts();

        updateSubmitButton();
    }

    /**
     * Currency Selection
     */
    function initCurrencySelection() {
        $('#currencyGroup .sola-option-btn').on('click', function () {
            const $btn = $(this);
            const currency = $btn.data('value');

            $('#currencyGroup .sola-option-btn').removeClass('active');
            $btn.addClass('active');

            currentCurrency = currency;
            $('#currency').val(currency);

            updateCurrencySymbols();
        });
    }

    function updateCurrencySymbols() {
        const symbol = currencySymbols[currentCurrency];
        $('.amount-symbol').text(symbol);
        $('.currency-symbol').text(symbol);
        updateSubmitButton();
    }

    /**
     * Donation Type
     */
    function initDonationType() {
        $('#donationTypeGroup .sola-option-btn').on('click', function () {
            const $btn = $(this);
            const type = $btn.data('value');

            $('#donationTypeGroup .sola-option-btn').removeClass('active');
            $btn.addClass('active');

            donationType = type;
            $('#donationType').val(type);

            if (type === 'monthly') {
                $('#chargeMonthlyRow').slideDown(300);
            } else {
                $('#chargeMonthlyRow').slideUp(300);
            }
        });
    }

    /**
     * Amount Selection - ENHANCED WITH SMOOTH TRANSITIONS
     */
    function initAmountSelection() {
        // Preset amounts
        $('.sola-amount-btn:not(.sola-custom-amount-btn)').on('click', function () {
            const $btn = $(this);
            const amount = $btn.data('value');

            $('.sola-amount-btn').removeClass('active');
            $btn.addClass('active');

            // Reset custom amount
            const $customBtn = $('#customAmountBtn');
            $customBtn.removeClass('active editing');
            $('#customAmountInput').hide().val('');
            $('.custom-label').show();
            $('.currency-symbol').hide();
            $('.edit-icon').show();

            currentAmount = amount;
            $('#amount').val(amount);
            updateSubmitButton();
        });

        // Custom amount button click - toggle edit mode
        $('#customAmountBtn').on('click', function () {
            const $btn = $(this);

            if (!$btn.hasClass('editing')) {
                // Enter edit mode
                $('.sola-amount-btn').removeClass('active');
                $btn.addClass('active editing');

                // Smooth transition
                $('.custom-label').fadeOut(150, function () {
                    $('.edit-icon').fadeOut(100);
                    $('.currency-symbol').fadeIn(200);
                    $('#customAmountInput').fadeIn(200, function () {
                        $(this).focus();
                    });
                });
            }
        });

        // Custom amount input
        $('#customAmountInput').on('input', function () {
            const value = $(this).val();
            if (value && parseFloat(value) > 0) {
                currentAmount = value;
                $('#amount').val(value);
                updateSubmitButton();
            }
        });

        // Handle focus loss - only exit edit mode if empty
        $('#customAmountInput').on('blur', function () {
            const value = $(this).val();
            if (!value || parseFloat(value) <= 0) {
                // Exit edit mode if empty
                setTimeout(() => {
                    const $customBtn = $('#customAmountBtn');
                    $customBtn.removeClass('editing');

                    $('#customAmountInput').fadeOut(150);
                    $('.currency-symbol').fadeOut(150, function () {
                        $('.custom-label').fadeIn(200);
                        $('.edit-icon').fadeIn(200);
                    });
                }, 200);
            }
        });

        // Global click outside  handler still useful for other buttons
        $(document).on('click', function (e) {
            const $customBtn = $('#customAmountBtn');
            if (!$(e.target).closest('#customAmountBtn').length &&
                !$(e.target).closest('.sola-amount-btn:not(.sola-custom-amount-btn)').length) {
                // Clicking outside - if custom is empty, reset it
                if ($customBtn.hasClass('editing') && !$('#customAmountInput').val()) {
                    $customBtn.removeClass('active editing');
                    $('#customAmountInput').hide().val('');
                    $('.custom-label').show();
                    $('.currency-symbol').hide();
                    $('.edit-icon').show();
                }
            }
        });
    }

    function updateSubmitButton() {
        const symbol = currencySymbols[currentCurrency];
        const displayAmount = currentAmount ? `${symbol}${currentAmount}` : `${symbol}0`;
        $('.amount-display').text(displayAmount);
    }

    /**
     * Card Validation - ENHANCED WITH LOGOS
     */
    function initCardValidation() {
        // Phone number formatting - only allow digits, +, -
        $('#phone').on('input', function () {
            let value = $(this).val();
            // Remove anything that's not a digit, +, or -
            value = value.replace(/[^0-9+\-]/g, '');
            $(this).val(value);
        });

        // Email validation with visual feedback
        $('#email').on('blur', function () {
            const email = $(this).val();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const $validationIcon = $(this).siblings('.sola-validation-icon');

            if (email && !emailRegex.test(email)) {
                $(this).addClass('error');
                $validationIcon.html(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                `).addClass('invalid').removeClass('valid');
            } else if (email) {
                $(this).removeClass('error');
                $validationIcon.html(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="9 12 11 14 15 10"></polyline>
                    </svg>
                `).addClass('valid').removeClass('invalid');
            } else {
                $(this).removeClass('error');
                $validationIcon.html('').removeClass('valid invalid');
            }
        });

        // Card number formatting and validation
        $('#cardNumber').on('input', function () {
            let value = $(this).val().replace(/\s/g, '');

            // Format with spaces
            if (value.length > 0) {
                value = value.match(/.{1,4}/g).join(' ');
                $(this).val(value);
            }

            // Detect card type and show logo
            const cardType = detectCardType(value.replace(/\s/g, ''));
            displayCardLogo(cardType);
        });

        // Expiry date formatting
        $('#expiry').on('input', function () {
            let value = $(this).val().replace(/\D/g, '');

            if (value.length >= 2) {
                value = value.substr(0, 2) + '/' + value.substr(2, 2);
            }

            $(this).val(value);
        });

        // CVV validation
        $('#cvv').on('input', function () {
            let value = $(this).val().replace(/\D/g, '');
            $(this).val(value);
        });
    }

    function detectCardType(number) {
        for (const [type, pattern] of Object.entries(cardPatterns)) {
            if (pattern.test(number)) {
                return type;
            }
        }
        return null;
    }

    function displayCardLogo(type) {
        const $logo = $('#cardTypeLogo');

        if (!type || !cardLogos[type]) {
            $logo.removeClass('visible').html('');
            return;
        }

        $logo.html(`<img src="${cardLogos[type]}" alt="${type}">`).addClass('visible');
    }

    /**
     * Payment Methods (Google Pay / Apple Pay)
     * Note: Requires merchant account verification and iFields integration
     */
    function initPaymentMethods() {
        // Check if Google Pay is available
        const googlePayAvailable = window.google && window.google.payments;
        if (googlePayAvailable) {
            console.log('Google Pay available in browser');
        }

        // Check if Apple Pay is available
        const applePayAvailable = window.ApplePaySession && ApplePaySession.canMakePayments();
        if (applePayAvailable) {
            console.log('Apple Pay available in browser');
        }

        // Google Pay handler - Placeholder for merchant verification
        $('.sola-wallet-btn.google-pay').on('click', function () {
            const message = currentLang === 'he'
                ? 'תשלום Google Pay זמין לאחר אימות merchant account.\n\nנדרש:\n• Merchant account מאושר\n• אינטגרציית iFields\n• אישור Google Pay'
                : 'Google Pay available after merchant account verification.\n\nRequired:\n• Verified merchant account\n• iFields integration\n• Google Pay approval';

            showMessage('error', message);
        });

        // Apple Pay handler - Placeholder for merchant verification
        $('.sola-wallet-btn.apple-pay').on('click', function () {
            const message = currentLang === 'he'
                ? 'תשלום Apple Pay זמין לאחר אימות merchant account.\n\nנדרש:\n• Merchant account מאושר\n• רישום דומיין באפל\n• HTTPS\n• אינטגרציית iFields'
                : 'Apple Pay available after merchant account verification.\n\nRequired:\n• Verified merchant account\n• Domain registered with Apple\n• HTTPS\n• iFields integration';

            showMessage('error', message);
        });

        // Future: Uncomment when merchant account is verified
        /*
        function initGooglePayIntegration() {
            // Load iFields library
            // Initialize Google Pay with iFields
            // Process payment token
        }
        
        function initApplePayIntegration() {
            // Load iFields library  
            // Initialize Apple Pay with iFields
            // Process payment token
        }
        */
    }

    /**
     * Format expiry date for Sola API (MMYY format)
     * Ensures month is zero-padded
     */
    function formatExpiryForAPI(expiryInput) {
        // Remove any non-digits
        let expiry = expiryInput.replace(/\D/g, '');

        // Should be 4 digits: MMYY
        if (expiry.length !== 4) {
            return expiry; // Return as-is if invalid length
        }

        // Extract month and year
        let month = expiry.substring(0, 2);
        let year = expiry.substring(2, 4);

        // Ensure month is zero-padded (01-12)
        if (month.length === 1) {
            month = '0' + month;
        }

        return month + year; // Return MMYY
    }

    /**
     * Form Submission
     */
    function initFormSubmission() {
        $('#solaDonationFormElement').on('submit', function (e) {
            e.preventDefault();

            if (validateForm()) {
                submitForm();
            }
        });
    }

    function validateForm() {
        let isValid = true;
        const $message = $('#formMessage');

        // Validation messages
        const messages = {
            he: {
                fillRequired: 'אנא מלא את כל השדות הנדרשים',
                invalidEmail: 'כתובת אימייל לא תקינה',
                selectAmount: 'אנא בחר סכום תרומה',
                invalidCard: 'מספר כרטיס לא תקין',
                invalidExpiry: 'תוקף לא תקין',
                invalidCVV: 'CVV לא תקין'
            },
            en: {
                fillRequired: 'Please fill all required fields',
                invalidEmail: 'Invalid email address',
                selectAmount: 'Please select a donation amount',
                invalidCard: 'Invalid card number',
                invalidExpiry: 'Invalid expiry date',
                invalidCVV: 'Invalid CVV'
            }
        };

        const msg = messages[currentLang];

        // Get all required fields
        const requiredFields = [
            'firstName', 'lastName', 'phone', 'email', 'address',
            'cardNumber', 'expiry', 'cvv'
        ];

        requiredFields.forEach(function (fieldName) {
            const $field = $(`#${fieldName}`);
            if (!$field.val() || $field.val().trim() === '') {
                isValid = false;
                $field.addClass('error');
            } else {
                $field.removeClass('error');
            }
        });

        // Email validation
        const email = $('#email').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            isValid = false;
            $('#email').addClass('error');
            showMessage('error', msg.invalidEmail);
            return false;
        }

        // Amount validation
        const amount = parseFloat($('#amount').val() || currentAmount);
        if (!amount || amount <= 0) {
            isValid = false;
            showMessage('error', msg.selectAmount);
            return false;
        }

        // Card number
        const cardNumber = $('#cardNumber').val().replace(/\s/g, '');
        if (cardNumber.length < 13) {
            isValid = false;
            showMessage('error', msg.invalidCard);
            return false;
        }

        // Expiry
        const expiry = $('#expiry').val();
        if (!/^\d{2}\/\d{2}$/.test(expiry)) {
            isValid = false;
            showMessage('error', msg.invalidExpiry);
            return false;
        }

        // CVV
        const cvv = $('#cvv').val();
        if (cvv.length < 3 || cvv.length > 4) {
            isValid = false;
            showMessage('error', msg.invalidCVV);
            return false;
        }

        if (!isValid) {
            showMessage('error', msg.fillRequired);
        }

        return isValid;
    }

    function submitForm() {
        const $btn = $('#submitBtn');
        const $message = $('#formMessage');

        // Success/Error messages
        const messages = {
            he: {
                success: 'תודה על תרומתך! העסקה בוצעה בהצלחה.',
                error: 'אירעה שגיאה בעיבוד התשלום. אנא נסה שוב.',
                networkError: 'אירעה שגיאה בתקשורת. אנא נסה שוב.'
            },
            en: {
                success: 'Thank you for your donation! Transaction completed successfully.',
                error: 'An error occurred processing the payment. Please try again.',
                networkError: 'A communication error occurred. Please try again.'
            }
        };

        const msg = messages[currentLang];

        $btn.addClass('loading').prop('disabled', true);
        $message.hide();

        const formData = {
            action: 'sola_donation_process',
            nonce: solaDonation.nonce,
            firstName: $('#firstName').val(),
            lastName: $('#lastName').val(),
            phone: $('#phone').val(),
            email: $('#email').val(),
            address: $('#address').val(),
            taxId: $('#taxId').val(),
            currency: currentCurrency,
            donationType: donationType,
            amount: parseFloat($('#amount').val() || currentAmount),
            cardNumber: $('#cardNumber').val().replace(/\s/g, ''),
            expiry: formatExpiryForAPI($('#expiry').val()),
            cvv: $('#cvv').val()
        };

        if (donationType === 'monthly') {
            formData.chargeDay = $('#chargeDay').val();
            formData.chargeNow = $('#chargeNow').is(':checked');
        }

        // Debug log (remove in production)
        console.log('Sending payment data:', {
            amount: formData.amount,
            currency: formData.currency,
            donationType: formData.donationType,
            expiryFormatted: formData.expiry,
            cardLast4: formData.cardNumber.slice(-4)
        });

        $.ajax({
            url: solaDonation.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function (response) {
                console.log('API Response:', response);

                if (response.success) {
                    showMessage('success', msg.success);

                    setTimeout(function () {
                        if (response.data.redirectUrl) {
                            window.location.href = response.data.redirectUrl;
                        } else {
                            $('#solaDonationFormElement')[0].reset();
                            goToStep(1);
                            $btn.removeClass('loading').prop('disabled', false);
                        }
                    }, 2000);
                } else {
                    console.error('Payment failed:', response);
                    showMessage('error', response.data.message || msg.error);
                    $btn.removeClass('loading').prop('disabled', false);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', { xhr, status, error });
                console.error('Response Text:', xhr.responseText);
                showMessage('error', msg.networkError);
                $btn.removeClass('loading').prop('disabled', false);
            }
        });
    }

    function showMessage(type, message) {
        const $message = $('#formMessage');
        $message
            .removeClass('success error')
            .addClass(type)
            .text(message)
            .slideDown(300);

        if (type === 'success') {
            setTimeout(function () {
                $message.slideUp(300);
            }, 5000);
        }
    }

})(jQuery);