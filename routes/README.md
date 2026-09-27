# Route structure

The Laravel route files will be split by concern as the application grows:

- web.php — storefront pages
- auth.php — authentication and verification
- customer.php — customer dashboard, wallet, orders and returns
- admin.php — unified administration dashboard
- api.php — internal/API endpoints

Authorization must be enforced server-side; UI visibility is not a security boundary.
