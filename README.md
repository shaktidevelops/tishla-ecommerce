# Tishla by Purnika Sales

New architecture: PHP 8.3 + Laravel 13 + MariaDB + Blade + Composer.

The main branch is now the new single Laravel application. The previous Next.js + FastAPI + PostgreSQL project is preserved in the branch legacy-next-fastapi-postgresql.

Included:
- Luxury English storefront shell
- Catalogue, departments, variants and media schema
- Retail and wholesale-ready customers
- Cart and COD checkout foundation
- Laravel session admin authentication
- Admin dashboard
- Product management
- Order management
- Customer directory
- Merchandising overview
- Media upload foundation
- Settings
- CMS/SEO tables for future modules
- MariaDB migrations and development seed data

Local requirements:
PHP 8.3+, Composer 2.x, MariaDB 10.3+, Git and VS Code.

Node.js, Python, PostgreSQL and Docker are not required.

Setup:
1. Create a MariaDB database named tishla.
2. Copy .env.example to .env.
3. Add TISHLA_ADMIN_PASSWORD with 8+ characters.
4. Run composer install.
5. Run php artisan key:generate.
6. Run php artisan migrate --seed.
7. Run php artisan serve.

Open http://127.0.0.1:8000 and /admin/login.

Never commit .env, database passwords, mail credentials or Razorpay secrets.
