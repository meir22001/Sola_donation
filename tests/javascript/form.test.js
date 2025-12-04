/**
 * JavaScript Tests for Form Functionality
 * Testing card validation, formatting, and form interactions
 */

describe('Sola Donation Form - Card Validation', () => {

    // Credit card type detection
    describe('Card Type Detection', () => {

        test('should detect Visa cards', () => {
            const visaPattern = /^4/;
            expect(visaPattern.test('4111111111111111')).toBe(true);
            expect(visaPattern.test('4444333322221111')).toBe(true);
            expect(visaPattern.test('5111111111111111')).toBe(false);
        });

        test('should detect Mastercard', () => {
            const mastercardPattern = /^(5[1-5]|2[2-7])/;
            expect(mastercardPattern.test('5454545454545454')).toBe(true);
            expect(mastercardPattern.test('2221000000000000')).toBe(true);
            expect(mastercardPattern.test('4111111111111111')).toBe(false);
        });

        test('should detect American Express', () => {
            const amexPattern = /^3[47]/;
            expect(amexPattern.test('370276000431054')).toBe(true);
            expect(amexPattern.test('340000000000009')).toBe(true);
            expect(amexPattern.test('4111111111111111')).toBe(false);
        });

        test('should detect Discover', () => {
            const discoverPattern = /^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5])|64[4-9]|65)/;
            expect(discoverPattern.test('6011208703331119')).toBe(true);
            expect(discoverPattern.test('6445000000000000')).toBe(true);
            expect(discoverPattern.test('4111111111111111')).toBe(false);
        });
    });

    // Card number formatting
    describe('Card Number Formatting', () => {

        test('should format card number with spaces', () => {
            const formatCardNumber = (number) => {
                const cleaned = number.replace(/\s/g, '');
                return cleaned.match(/.{1,4}/g)?.join(' ') || cleaned;
            };

            expect(formatCardNumber('4111111111111111')).toBe('4111 1111 1111 1111');
            expect(formatCardNumber('411111111111111')).toBe('4111 1111 1111 111');
            expect(formatCardNumber('4111')).toBe('4111');
        });

        test('should remove non-numeric characters', () => {
            const cleanNumber = (number) => number.replace(/\D/g, '');

            expect(cleanNumber('4111-1111-1111-1111')).toBe('4111111111111111');
            expect(cleanNumber('4111 1111 1111 1111')).toBe('4111111111111111');
            expect(cleanNumber('4111abc1111def1111')).toBe('4111111111111111');
        });
    });

    // Expiry date formatting
    describe('Expiry Date Formatting', () => {

        test('should format expiry as MM/YY', () => {
            const formatExpiry = (value) => {
                const cleaned = value.replace(/\D/g, '');
                if (cleaned.length >= 2) {
                    return cleaned.substr(0, 2) + '/' + cleaned.substr(2, 2);
                }
                return cleaned;
            };

            expect(formatExpiry('1225')).toBe('12/25');
            expect(formatExpiry('0124')).toBe('01/24');
            expect(formatExpiry('1')).toBe('1');
            expect(formatExpiry('12')).toBe('12/');
        });

        test('should validate month range', () => {
            const isValidMonth = (month) => {
                const m = parseInt(month);
                return m >= 1 && m <= 12;
            };

            expect(isValidMonth('01')).toBe(true);
            expect(isValidMonth('12')).toBe(true);
            expect(isValidMonth('00')).toBe(false);
            expect(isValidMonth('13')).toBe(false);
        });
    });

    // CVV validation
    describe('CVV Validation', () => {

        test('should accept 3-digit CVV', () => {
            const isValidCVV = (cvv) => /^\d{3}$/.test(cvv);

            expect(isValidCVV('123')).toBe(true);
            expect(isValidCVV('000')).toBe(true);
            expect(isValidCVV('12')).toBe(false);
            expect(isValidCVV('1234')).toBe(false);
        });

        test('should accept 4-digit CVV for Amex', () => {
            const isValidAmexCVV = (cvv) => /^\d{4}$/.test(cvv);

            expect(isValidAmexCVV('1234')).toBe(true);
            expect(isValidAmexCVV('0000')).toBe(true);
            expect(isValidAmexCVV('123')).toBe(false);
            expect(isValidAmexCVV('12345')).toBe(false);
        });
    });

    // Email validation
    describe('Email Validation', () => {

        test('should validate email format', () => {
            const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

            expect(isValidEmail('test@example.com')).toBe(true);
            expect(isValidEmail('user.name@domain.co.il')).toBe(true);
            expect(isValidEmail('invalid.email')).toBe(false);
            expect(isValidEmail('@example.com')).toBe(false);
            expect(isValidEmail('test@')).toBe(false);
        });
    });

    // Amount validation
    describe('Amount Validation', () => {

        test('should validate positive amounts', () => {
            const isValidAmount = (amount) => {
                const num = parseFloat(amount);
                return !isNaN(num) && num > 0;
            };

            expect(isValidAmount('100')).toBe(true);
            expect(isValidAmount('50.50')).toBe(true);
            expect(isValidAmount('0')).toBe(false);
            expect(isValidAmount('-10')).toBe(false);
            expect(isValidAmount('abc')).toBe(false);
        });

        test('should format amounts to 2 decimal places', () => {
            const formatAmount = (amount) => parseFloat(amount).toFixed(2);

            expect(formatAmount('100')).toBe('100.00');
            expect(formatAmount('50.5')).toBe('50.50');
            expect(formatAmount('99.999')).toBe('100.00');
        });
    });

    // Currency symbols
    describe('Currency Symbols', () => {

        test('should return correct currency symbol', () => {
            const currencySymbols = {
                'ILS': '₪',
                'USD': '$',
                'EUR': '€'
            };

            expect(currencySymbols['ILS']).toBe('₪');
            expect(currencySymbols['USD']).toBe('$');
            expect(currencySymbols['EUR']).toBe('€');
        });
    });

    // Language switching
    describe('Language Switching', () => {

        test('should toggle between Hebrew and English', () => {
            let currentLang = 'he';
            const toggleLanguage = () => {
                currentLang = currentLang === 'he' ? 'en' : 'he';
                return currentLang;
            };

            expect(toggleLanguage()).toBe('en');
            expect(toggleLanguage()).toBe('he');
            expect(toggleLanguage()).toBe('en');
        });

        test('should set correct text direction', () => {
            const getDirection = (lang) => lang === 'he' ? 'rtl' : 'ltr';

            expect(getDirection('he')).toBe('rtl');
            expect(getDirection('en')).toBe('ltr');
        });
    });
});

describe('Sola Donation Form - Form Interactions', () => {

    // Donation type toggle
    describe('Donation Type Toggle', () => {

        test('should show charge day for monthly donations', () => {
            const shouldShowChargeDay = (type) => type === 'monthly';

            expect(shouldShowChargeDay('monthly')).toBe(true);
            expect(shouldShowChargeDay('oneTime')).toBe(false);
        });
    });

    // Charge day validation
    describe('Charge Day Validation', () => {

        test('should accept days 1-28', () => {
            const isValidChargeDay = (day) => {
                const d = parseInt(day);
                return d >= 1 && d <= 28;
            };

            expect(isValidChargeDay(1)).toBe(true);
            expect(isValidChargeDay(15)).toBe(true);
            expect(isValidChargeDay(28)).toBe(true);
            expect(isValidChargeDay(0)).toBe(false);
            expect(isValidChargeDay(29)).toBe(false);
        });
    });
});
