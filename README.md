# Surat NX

Laravel 11/12 + MySQL storefront and admin panel for a fabric e-commerce store.

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, and `fileinfo` extensions.
- Composer 2, MySQL 8+ or MariaDB 10.6+.
- XAMPP users: start **MySQL** from the XAMPP Control Panel. Apache is optional when using `php artisan serve`.

## Start on Windows / XAMPP

1. Open PowerShell in this project directory. Confirm PHP and Composer are available:

	```powershell
	php -v
	composer --version
	```

2. Create the database in phpMyAdmin (`http://localhost/phpmyadmin`) or a MySQL client. Use this SQL:

	```sql
	CREATE DATABASE suratnx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
	```

3. This workspace has a local `.env` already. If it is missing (for example, after extracting a clean copy), create it and edit the database credentials:

	```powershell
	Copy-Item .env.example .env
	```

	Set `DB_DATABASE=suratnx`, `DB_USERNAME` and `DB_PASSWORD` to match MySQL. A default XAMPP install commonly uses `root` with an empty password; use the credentials configured on your machine. Never commit `.env`.

4. Install PHP packages and initialize the database:

	```powershell
	composer install
	php artisan key:generate
	php artisan optimize:clear
	php artisan migrate --seed
	php artisan storage:link
	```

	`key:generate` writes a fresh application key into `.env`. The seeder creates sample catalog data, an admin account, and a development-only demo customer.

5. Start the Laravel development server:

	```powershell
	php artisan serve
	```

	Open `http://127.0.0.1:8000`. Sign in to the admin panel at `/admin/login` with `admin@suratnx.com` and the `ADMIN_PASSWORD` value in `.env`. The seeded development customer is `demo@suratnx.com` / `demo123`; remove or change it before production.

6. In a second terminal, run the scheduler while developing so expired unpaid payment orders release reserved stock:

	```powershell
	php artisan schedule:work
	```

## Email and Payments

- Without SMTP credentials, `MAIL_MAILER=log` records outgoing email in `storage/logs/laravel.log`; the app can still be tested locally. For real delivery, set `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, and a verified `MAIL_FROM_ADDRESS` in `.env`.
- Customer profile pictures are validated as images up to 5 MB and stored on Laravel's public disk; run `php artisan storage:link` so uploaded pictures can be displayed.
- Storefront and admin forms use Laravel validation feedback. Administrators sign in separately at `/admin/login`; storefront customer account pages are not available to admin sessions.
- Password recovery emails a six-digit OTP. Codes expire after 10 minutes and allow up to five verification attempts.
- Checkout uses Cash on Delivery until `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, and `RAZORPAY_WEBHOOK_SECRET` are configured. Razorpay test mode should be verified before using live credentials. Register `https://your-domain/webhooks/razorpay` with the `payment.captured` event.
- Stripe and PayPal values in `.env.example` are placeholders; those gateways are not integrated. Gateway refunds are not automated.

## Common Setup Errors

- `SQLSTATE[HY000] [1049] Unknown database`: create `suratnx` and verify `DB_DATABASE` in `.env`.
- `SQLSTATE[HY000] [1045] Access denied`: correct `DB_USERNAME` and `DB_PASSWORD`, then run `php artisan config:clear`.
- `No application encryption key`: run `php artisan key:generate`.
- Product images return 404: run `php artisan storage:link` and confirm `FILESYSTEM_DISK=public`.
- Mail is not appearing in an inbox: with `MAIL_MAILER=log`, inspect `storage/logs/laravel.log`; use valid SMTP settings for inbox delivery.

For Linux/macOS or production deployment, security requirements, test commands, and complete feature details, see [SETUP.md](SETUP.md). Do not use the development server or demo credentials for production.
