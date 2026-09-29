# Tishla Windows setup

Install PHP 8.3+, Composer 2.x, MariaDB, Git and VS Code.

Keep the old Python, Node.js and PostgreSQL installations until the Laravel application has passed local testing.

Verify:
php -v
composer --version
mariadb --version
git --version

Create an empty MariaDB database named tishla.

Copy .env.example to .env and set:
DB_DATABASE=tishla
DB_USERNAME=your_mariadb_user
DB_PASSWORD=your_mariadb_password
TISHLA_ADMIN_EMAIL=admin@tishla.com
TISHLA_ADMIN_NAME="Shakti Develops"
TISHLA_ADMIN_PASSWORD=choose-a-password-of-8-or-more-characters

Run:
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve

Open http://127.0.0.1:8000/admin/login.

No Node build step is required.
