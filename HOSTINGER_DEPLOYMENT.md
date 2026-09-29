# Tishla Hostinger deployment

Target: PHP 8.3 + Laravel 13 + MariaDB + Git deployment + HTTPS.

1. Create the Hostinger MariaDB database and user.
2. Deploy the GitHub repository.
3. Keep the Laravel project root outside the public document root where possible.
4. Point the domain document root at the project public directory.
5. Create the production .env.
6. Run composer install --no-dev --optimize-autoloader.
7. Run php artisan migrate --force.
8. Seed only safe production defaults and disable demo data.
9. Configure mail, Razorpay and shipping credentials.
10. Enable HTTPS and verify /, /shop, /admin/login, /checkout and /up.

Never commit .env or live credentials.
