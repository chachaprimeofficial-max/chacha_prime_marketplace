# Chacha Prime Production Checklist

## Application
- Install PHP 8.1+ and Composer.
- Run composer install --no-dev --optimize-autoloader.
- Set APP_KEY and database credentials.
- Point the web server document root to /public.
- Run php artisan config:cache and php artisan route:cache.
- Ensure storage/framework/{cache,sessions,views} is writable.

## Database
Import manually, in order:
1. database/sql/01_schema.sql
2. database/sql/b2b_verification.sql
3. database/sql/checkout_payments.sql
4. database/sql/inventory_management.sql
5. database/sql/invoices_tax.sql
6. database/sql/notifications.sql
7. database/sql/payment_center.sql
8. database/sql/payment_lifecycle.sql
9. database/sql/payment_providers.sql
10. database/sql/payment_refunds.sql
11. database/sql/returns_refunds.sql
12. database/sql/security_auth.sql
13. database/sql/shipping_fulfillment.sql
14. database/sql/shipping_management.sql
15. database/sql/support_tickets.sql
Do not import database/sql/chacha_prime_master_schema.sql; it is legacy.

## Payments
Configure provider secrets in the server environment or encrypted settings. Never store raw card numbers or CVV.

## Security
Use HTTPS, secure cookies, a strong APP_KEY, production APP_DEBUG=false, restricted admin roles, private document storage, and regular database backups.

## Final QA
Verify registration/login/2FA, product browsing, cart, checkout, payment webhook, wallet payment, order lifecycle, shipment events, group-buy success/failure/refund, returns/refunds, invoices, reviews, wishlist, business verification, support tickets and admin audit logs.
