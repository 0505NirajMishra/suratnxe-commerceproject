# Surat NX — Complete Laravel + MySQL E-commerce Project

This package contains the Laravel application source, MySQL migrations/seed data, storefront and admin panel views, original design CSS assets, and server-side database-backed workflows. Browser localStorage/sessionStorage is not used for business data. Cart line items are stored in MySQL and associated with the Laravel session ID; products, categories, accounts, addresses, orders, stock, wishlists, coupons, enquiries, newsletter subscriptions and store settings are persisted in MySQL.

## Requirements
- PHP 8.2 or newer and Composer 2
- MySQL 8+ / MariaDB 10.6+
- PHP extensions required by Laravel (PDO MySQL, Mbstring, OpenSSL, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo)

## Install (Windows/XAMPP or Linux)
1. Extract the ZIP into a folder, e.g. `C:\xampp\htdocs\suratnx`.
2. Open terminal in that folder and run:
   ```bash
   composer install
   copy .env.example .env   # Windows CMD; on PowerShell use Copy-Item .env.example .env
   php artisan key:generate
   ```
3. Create a MySQL database named `suratnx` with utf8mb4 collation. Update `.env` database username/password and set a strong `ADMIN_PASSWORD`.
4. Run:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   php artisan serve
   ```
5. Open `http://127.0.0.1:8000`.

## Demo sign-in
- Admin: `admin@suratnx.com` / value of `ADMIN_PASSWORD` in `.env` (the seeder's development fallback is `admin123`; change it before use).
- Customer demo: `demo@suratnx.com` / `demo123` (development only; remove/change before deployment).

## Included workflows
- Responsive storefront, database-backed categories/products/search/filter/sorting, product details and related fabrics.
- Cart quantity changes, coupon validation, shipping totals, server-side price/stock checks, transactional order placement.
- Registration/login/logout, password reset by email, profile update, password change, saved checkout addresses, account order list, return requests, and order tracking.
- Verified-purchase product reviews with one review per customer/product and dynamically updated product ratings.
- SMTP-ready enquiry, newsletter, order-confirmation, payment-confirmation, and fulfilment-status emails.
- Admin-editable homepage hero, About introduction, customer policies, contact details, shipping fees, and GST.
- Admin login with role middleware, dashboard metrics, fabric CRUD/multi-image uploads, categories, order status/courier/tracking updates, customers, coupons, enquiries and store settings.
- Contact form and newsletter subscriptions saved in MySQL; CSRF protection and validation on forms.

## Important production configuration
- Email stays on Laravel's `log` mailer until configured. To deliver real mail, set `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and a verified `MAIL_FROM_ADDRESS` in `.env`. Use the SMTP provider's values; use `MAIL_SCHEME=smtp` for port 587/STARTTLS or `smtps` for implicit TLS on port 465. Enquiry notifications go to the Store email configured in Admin > Settings, with an acknowledgement to the customer. New subscriptions and order/payment/status changes also send mail. Delivery failures are logged without discarding saved enquiries or orders.
- Razorpay is the only online gateway wired in this project. Checkout offers COD only until `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, and `RAZORPAY_WEBHOOK_SECRET` are all configured. For test mode, set the test key pair in `.env`; register `https://your-domain/webhooks/razorpay` in Razorpay with the `payment.captured` event and use its webhook secret. The webhook validates Razorpay's signature. Verify test payments end-to-end before switching to live keys. Stripe and PayPal environment placeholders are not integrations.
- Unpaid Razorpay orders reserve stock for 30 minutes and are then cancelled by the Laravel scheduler. Run `php artisan schedule:work` while developing. In production, run `php artisan schedule:run` once per minute using cron or Windows Task Scheduler.
- Automatic gateway refunds are not implemented. Cancelling a paid order does not mark it refunded; process the refund in the payment provider, then update the order's payment status in Admin.
- The configured GST percentage is applied to discounted product value; shipping is excluded. Product-specific HSN slabs and tax-inclusive pricing are not modeled, so confirm the applicable tax treatment before production.
- Store identity, homepage/About/policy/footer copy, and social URLs are editable in Admin > Settings. UI labels, layout structure, and some product fallback text remain code-defined; this is a focused store-content editor, not a general page builder.
- Customers may request a return within the configurable Admin > Settings delivery window (1-60 days); admins can review and update status. Return approval does not issue a payment-provider refund.
- Email is sent synchronously. Delivery failures are logged, but there is no queue/retry dashboard yet.
- Set `APP_DEBUG=false`, use HTTPS, change demo credentials, configure backups and a production session/cache driver before launch. Keep all payment/SMTP secrets in `.env`, never in the database, source control, or browser code. After changing environment values, run `php artisan config:clear`.
- For local uploaded images, `php artisan storage:link` is required.
- Existing design CSS is preserved under `public/css`. The old localStorage-based JavaScript has been excluded from the runnable project. The live pages use Blade forms and database-backed workflows; JavaScript is limited to UI interactions.

## Validation status
The source was not runtime-tested in this environment because PHP/Composer and a configured MySQL server are unavailable. Run `php artisan route:list`, `php artisan migrate --seed`, `php artisan test`, and end-to-end test-mode email/payment checks in your configured environment before production use.
