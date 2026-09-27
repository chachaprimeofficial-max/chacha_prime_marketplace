# Chacha Prime Marketplace

Premium single-vendor commerce platform for B2C, B2B/Wholesale and Group Buying.

## Stack
- PHP / Laravel
- MySQL
- Blade, HTML, CSS, JavaScript
- Gemini AI integration (server-side)

## Database policy
Laravel migration files are intentionally not used. The database is maintained as manually importable SQL under `database/sql/`.

## Core modules
- Premium storefront
- Customer dashboard
- Unified admin dashboard
- B2B verification and wholesale pricing
- Group buying with wallet refund on failed conditions
- Customer wallet and immutable ledger
- Product SKU and automatic QR codes
- Premium QR invoices
- Orders, tracking, returns and replacements
- Shipping templates and courier integrations
- Chacha AI assistant
- Product sourcing/import workflow
- AI-assisted category and product content

## Installation
1. Run `composer install`.
2. Copy `.env.example` to `.env` and configure secrets.
3. Run `php artisan key:generate`.
4. Import `database/sql/01_schema.sql` manually into MySQL.
5. Import seed SQL files as required.
6. Configure the web server document root to `public/`.

Never commit database, SMTP, Gemini, payment, shipping, or other production secrets.
