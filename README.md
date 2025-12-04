# Sola Donation Plugin

Professional bilingual (Hebrew/English) WordPress donation plugin with Sola Payments integration.

## Features

- **Bilingual Support**: Complete Hebrew and English translations with instant language switching
- **RTL/LTR Support**: Automatic direction switching for proper Hebrew display
- **Glassmorphism Design**: Modern, elegant UI with glassmorphism effects and smooth animations
- **Payment Processing**: Sola Payments integration for secure credit card processing
- **Recurring Donations**: Support for monthly recurring donations with customizable charge day
- **One-Time Donations**: Quick one-time donation processing
- **Multi-Currency**: Support for ILS (₪), USD ($), and EUR (€)
- **Secure**: PCI-compliant payment processing through Sola Payments gateway
- **Responsive**: Mobile-friendly design that works on all devices
- **Webhooks**: Post-submission webhook notifications for integration with other systems
- **Redirect**: Customizable redirect after successful donation

## Installation

1. Upload the `donation` folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Sola Donations' → 'Settings' to configure your API keys

## Configuration

### API Settings

1. Sign up for a [Sola Payments account](https://solapayments.com)
2. Get your API keys (xKey) from the Sola dashboard
3. In WordPress admin, go to **Sola Donations** → **Settings**
4. Enter your **Sandbox xKey** (for testing)
5. Enter your **Production xKey** (for live transactions)
6. Toggle **Sandbox Mode** on/off as needed

### Post-Submission Actions

- **Redirect URL**: Enter a URL where users should be redirected after successful donation (optional)
- **Webhook URL**: Enter a URL to receive transaction notifications (optional)

## Usage

Add the donation form to any page or post using the shortcode:

```
[sola_donation_form]
```

## Form Fields

### Personal Details
- First Name
- Last Name
- Phone
- Email
- Address

### Donation Details
- Currency (ILS / USD / EUR)
- Donation Type (Monthly / One-Time)
- Amount (Preset: 50, 100, 180, 500 or Custom)
- Charge Day (Monthly only, 1-28)
- Charge first donation now checkbox (Monthly only)

### Payment Details
- Credit Card Number
- Expiry Date (MM/YY)
- CVV

## Testing

### Sandbox Mode

1. Enable **Sandbox Mode** in settings
2. Use your **Sandbox xKey**
3. Use test card numbers:
   - Visa: `4444333322221111`
   - Visa: `4111111111111111`
   - Mastercard: `5454545454545454`
   - Discover: `6011208703331119`
   - Amex: `370276000431054`
4. Use any future expiry date (e.g., 12/25)
5. Use any 3-digit CVV (123)

### Going Live

1. Disable **Sandbox Mode** in settings
2. Switch to your **Production xKey**
3. Test with a small real transaction
4. Verify transaction appears in your Sola Payments dashboard

## Design Features

### Colors
- Primary Blue: `#023047`
- Accent Orange: `#F49431`
- White backgrounds with glassmorphism effects

### Fonts
- Hebrew: Noto Sans Hebrew
- English: Inter
- Numbers: Outfit

### Animations
- Smooth hover effects on all interactive elements
- Floating background elements
- Continuous pulse animation on donate button
- Card type detection with logo display
- Form validation with visual feedback

## Webhook Payload

When a webhook URL is configured, the plugin sends POST requests with the following JSON structure:

```json
{
  "event": "donation_successful",
  "timestamp": "2024-12-02 15:30:45",
  "donation": {
    "type": "monthly",
    "amount": 100,
    "currency": "ILS",
    "recurring": {
      "chargeDay": 15,
      "chargeNow": true
    }
  },
  "donor": {
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "phone": "123-456-7890",
    "address": "123 Main St"
  },
  "transaction": {
    "refNum": "ABC123",
    "authAmount": "100.00",
    "authCode": "XYZ789",
    "maskedCard": "XXXXXXXXXXXX1111",
    "cardType": "Visa"
  }
}
```

## Recurring Donations

Recurring donations are managed through:
1. Token generation via Sola Payments `cc:Save` command
2. Storage in WordPress custom post type `sola_recurring_donation`
3. Accessible from WordPress admin under **Sola Donations** → **Recurring Donations**

**Note**: Actual recurring charges need to be scheduled via cron job or external system using the stored tokens.

## File Structure

```
donation/
├── sola-donation-plugin.php    # Main plugin file
├── admin/
│   ├── settings-page.php       # Admin settings interface
│   └── admin-styles.css        # Admin styling
├── public/
│   ├── form-template.php       # Form HTML template
│   ├── form-styles.css         # Form styling
│   └── form-script.js          # Form JavaScript
├── includes/
│   ├── api-handler.php         # Sola API integration
│   └── webhook-handler.php     # Webhook functionality
└── README.md                   # This file
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- HTTPS (recommended for security)
- Sola Payments account

## Support

For issues with:
- **Plugin**: Check WordPress debug logs
- **Payments**: Contact Sola Payments support
- **API**: Review [Sola Payments Documentation](https://docs.solapayments.com)

## Security

- Credit card data is sent directly to Sola Payments
- No sensitive data stored on WordPress server
- HTTPS recommended
- Nonce verification on all forms
- Input sanitization and validation
- Webhook signature verification

## License

GPL v2 or later

## Credits

- Designed for Hebrew and English speaking audiences
- Sola Payments integration
- Glassmorphism UI design
- Responsive and accessible

---

**Version**: 1.0.0  
**Author**: Your Name  
**Plugin URI**: https://solapayments.com
