# Lodge Subscription Plugin for Mautic

This Mautic plugin helps manage lodge membership subscriptions, including payment tracking, Stripe integration, and automated year-end processing.

## Features

- Track annual subscription payments for lodge members
- Set subscription rates for each year
- Process payments via Stripe or record manual payments
- Generate payment links for email campaigns
- Automated year-end processing to move unpaid subscriptions to arrears
- Dashboard with subscription statistics
- Export payment reports

## Requirements

- Mautic 6.0.1 or higher
- PHP 7.4 or higher
- Stripe account for online payments

## Installation

1. Download or clone this repository to the `plugins` directory of your Mautic installation
2. Clear the Mautic cache:
   ```
   php app/console cache:clear
   ```
3. Install the plugin:
   ```
   php app/console mautic:plugins:install
   ```
4. Go to the Plugins page in Mautic, find "Lodge Subscription Manager" and click "Configure"
5. Enter your Stripe API keys and save the configuration

## Initial Setup

### Custom Fields

The plugin requires specific custom fields to track subscription status. These should be created under a field group called "lodge_subscriptions":

- `craft_YEAR_due` (boolean) - Set if member SHOULD pay dues for that year
- `craft_YEAR_paid` (boolean) - Set if member HAS paid dues for that year
- `craft_paid_current` (boolean) - Set if member has paid current year dues
- `craft_owed_current` (number) - Amount member owes for current year
- `craft_owed_arrears` (number) - Amount member owes for previous years

The plugin will automatically create year-specific fields when running the year-end process.

### Subscription Rates

Before using the plugin, set up subscription rates for the current and next year:

1. Go to "Lodge Subscriptions" > "Subscription Rates"
2. Click "New Rate"
3. Enter the year and the subscription amount
4. Save the rate

## Usage

### Recording Payments

1. Go to a contact's detail view
2. Click the "Record Subscription Payment" button
3. Enter payment details and save

### Online Payments

1. Create an email template with the payment link token
2. Use the token `{lodge.payment_link}` in your email
3. Send to a segment of members with outstanding balances

### Year-End Processing

At the end of each year, run the year-end process:

1. Ensure the next year's subscription rate is set
2. Go to "Lodge Subscriptions" > "Dashboard"
3. Click "Run Year-End Process"
4. Alternatively, run the console command:
   ```
   php app/console lodge:subscription:yearend --year=CURRENT_YEAR
   ```

## Email Tokens

The following tokens can be used in email templates:

- `{lodge.subscription_amount}` - Current subscription amount
- `{lodge.subscription_arrears}` - Amount owed in arrears
- `{lodge.subscription_total}` - Total amount due
- `{lodge.payment_link}` - Stripe payment link

## Landing Pages

The plugin includes custom landing pages for payment success and cancellation. Create pages in Mautic with these aliases:

- `lodge-payment-success`
- `lodge-payment-cancel`

## License

[MIT License](LICENSE)

## Support

For support, please contact the plugin author or open an issue on GitHub. 