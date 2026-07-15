# Settle Pe

Settle Pe is a customer and case-management application for credit-report review, paid consultations, and debt-settlement assistance.

## Main workflows

- WhatsApp OTP registration and login
- CRIF credit-report retrieval and authentication
- Razorpay consultation payments and webhook verification
- Customer credit dashboard and settlement case portal
- Sales, relationship-manager, and administrator CRM workspaces

## Local setup

1. Copy `.env.example` to `.env` and configure the database and service credentials.
2. Run `composer install` and `npm install`.
3. Run `php artisan key:generate` and `php artisan migrate`.
4. Start the app with `composer run dev`.

The default database seeder intentionally creates no accounts. Provision team users explicitly with unique credentials.
