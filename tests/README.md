# Test Suite - מדריך הרצת בדיקות

## תוכן - Contents

1. [PHPUnit Tests (PHP)](#phpunit-tests)
2. [Jest Tests (JavaScript)](#jest-tests)
3. [Manual Testing](#manual-testing)
4. [Test Coverage](#test-coverage)

---

## PHPUnit Tests

### Installation - התקנה

```bash
cd /home/marko/projects/donation
composer require --dev phpunit/phpunit
composer require --dev yoast/phpunit-polyfills
```

### Setup WordPress Test Environment - הגדרת סביבת בדיקות

```bash
# Download WordPress test library
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest

# Or manually:
# 1. Install WordPress test suite
# 2. Set WP_TESTS_DIR environment variable
```

### Running PHPUnit Tests - הרצת בדיקות

```bash
# Run all tests
vendor/bin/phpunit -c tests/phpunit.xml

# Run specific test file
vendor/bin/phpunit tests/unit/test-plugin-activation.php

# Run with coverage
vendor/bin/phpunit -c tests/phpunit.xml --coverage-html coverage/
```

### Test Files - קבצי בדיקה

| קובץ | File | Description |
|------|------|-------------|
| `test-plugin-activation.php` | Plugin Activation | בדיקת הפעלה והגדרות ברירת מחדל |
| `test-api-handler.php` | API Handler | בדיקת ולידציה ועיבוד נתונים |
| `test-webhook-handler.php` | Webhook Handler | בדיקת payloads וחתימות |
| `test-settings.php` | Settings | בדיקת sanitization ושמירה |

### Expected Results - תוצאות צפויות

```
PHPUnit 9.x
...............................

Time: 00:02.345, Memory: 20.00 MB

OK (31 tests, 87 assertions)
```

---

## Jest Tests

### Installation - התקנה

```bash
cd tests/javascript
npm install
```

### Running Jest Tests - הרצת בדיקות

```bash
# Run all tests
npm test

# Run with watch mode
npm run test:watch

# Run with coverage
npm run test:coverage
```

### Test Suites - חבילות בדיקה

1. **Card Validation** - ולידציה של כרטיסי אשראי:
   - Card type detection (Visa, MC, Amex, Discover)
   - Card number formatting
   - Expiry date formatting
   - CVV validation

2. **Form Interactions** - אינטראקציות טופס:
   - Email validation
   - Amount validation
   - Currency symbols
   - Language switching

3. **Donation Type** - סוגי תרומה:
   - Monthly/One-time toggle
   - Charge day validation

### Expected Results - תוצאות צפויות

```
PASS tests/javascript/form.test.js
  Sola Donation Form - Card Validation
    Card Type Detection
      ✓ should detect Visa cards (3 ms)
      ✓ should detect Mastercard (1 ms)
      ✓ should detect American Express (1 ms)
      ✓ should detect Discover (1 ms)
    Card Number Formatting
      ✓ should format card number with spaces (2 ms)
      ✓ should remove non-numeric characters (1 ms)
    ...

Test Suites: 1 passed, 1 total
Tests:       26 passed, 26 total
```

---

## Manual Testing

### Full Manual Test Guide - מדריך בדיקות ידניות מלא

See: [`MANUAL_TESTING.md`](./MANUAL_TESTING.md)

**40 בדיקות מפורטות - 40 Detailed Tests:**

1-2. Installation & Setup
3-5. Admin Interface
6-18. Donation Form
19-24. Design & Animations
25-31. Payment Processing
32-35. Responsive Design
36-40. Browser Compatibility

### Quick Manual Test - בדיקה מהירה

```
✅ 1. הפעל תוסף
✅ 2. הזן API keys בהגדרות
✅ 3. צור עמוד עם [sola_donation_form]
✅ 4. בדוק החלפת שפות
✅ 5. מלא טופס ושלח (sandbox)
✅ 6. וודא הודעת הצלחה
```

---

## Test Coverage

### PHP Unit Tests Coverage

| Component | Tests | Coverage |
|-----------|-------|----------|
| Plugin Activation | 6 tests | ~90% |
| API Handler | 12 tests | ~85% |
| Webhook Handler | 5 tests | ~80% |
| Settings | 8 tests | ~90% |
| **Total** | **31 tests** | **~86%** |

### JavaScript Tests Coverage

| Component | Tests | Coverage |
|-----------|-------|----------|
| Card Validation | 10 tests | ~95% |
| Form Interactions | 8 tests | ~90% |
| Language/Currency | 6 tests | ~85% |
| Donation Type | 2 tests | ~80% |
| **Total** | **26 tests** | **~88%** |

### Manual Tests Coverage

| Category | Tests |
|----------|-------|
| Installation | 2 |
| Admin | 3 |
| Form | 13 |
| Design | 6 |
| Payment | 7 |
| Responsive | 4 |
| Browsers | 5 |
| **Total** | **40** |

---

## Continuous Integration (Optional)

### GitHub Actions Example

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  phpunit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - name: Install dependencies
        run: composer install
      - name: Run PHPUnit
        run: vendor/bin/phpunit -c tests/phpunit.xml

  jest:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node
        uses: actions/setup-node@v2
        with:
          node-version: '16'
      - name: Install dependencies
        run: cd tests/javascript && npm install
      - name: Run Jest
        run: cd tests/javascript && npm test
```

---

## Test Reports

### Generate HTML Coverage Report

**PHP:**
```bash
vendor/bin/phpunit -c tests/phpunit.xml --coverage-html tests/coverage/php
open tests/coverage/php/index.html
```

**JavaScript:**
```bash
cd tests/javascript
npm run test:coverage
open coverage/lcov-report/index.html
```

---

## Troubleshooting - פתרון בעיות

### PHPUnit: "Class not found"
**Solution:**
```bash
composer dump-autoload
```

### PHPUnit: "WordPress test library not found"
**Solution:**
```bash
# Download WordPress tests
svn co https://develop.svn.wordpress.org/trunk/ /tmp/wordpress
svn co https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/ /tmp/wordpress-tests-lib
```

### Jest: "Cannot find module"
**Solution:**
```bash
cd tests/javascript
rm -rf node_modules package-lock.json
npm install
```

### Manual: "Form not displaying"
**Solution:**
1. Check shortcode: `[sola_donation_form]`
2. Clear WordPress cache
3. Check browser console for JS errors

---

## Test Checklist - רשימת בדיקה

### Before Release - לפני שחרור:

- [ ] All PHPUnit tests pass
- [ ] All Jest tests pass
- [ ] Manual tests completed (40/40)
- [ ] Tested on Chrome, Firefox, Safari
- [ ] Tested on mobile devices
- [ ] Sandbox payment successful
- [ ] Webhook tested
- [ ] RTL/LTR verified
- [ ] Hebrew translations checked
- [ ] English translations checked
- [ ] No console errors
- [ ] No PHP warnings/errors

### After Release - אחרי שחרור:

- [ ] Production payment tested ($1)
- [ ] Live webhook tested
- [ ] User feedback collected
- [ ] Performance monitored
- [ ] Error logs checked

---

## Contact & Support

For test failures or questions:
- Check WordPress debug.log
- Check browser console
- Review test output
- Contact plugin developer

---

**Happy Testing! בדיקות מוצלחות!** ✅
