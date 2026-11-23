<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Centro Laundry system setup

The application includes customer accounts, staff/admin access, appointment requests, laundry order tracking, receipts, payment records, monthly reports, SMS notifications, and database backup tools.

1. Create a MySQL database named `centrolaundry_db` and configure the local `.env` file:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=centrolaundry_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Set `DB_USERNAME` and `DB_PASSWORD` to the credentials configured for your MySQL server. Keep real credentials in `.env`, not in source control.

2. Install dependencies and create the system tables:

   ```sh
   composer install
   php artisan key:generate
   php artisan migrate
   ```

   Existing records in a previous SQLite database are not copied automatically.

3. Create the first administrator from the server console. The command securely prompts for an account name, username, email, and password:

   ```sh
   php artisan laundry:admin
   ```

4. Start the application:

   ```sh
   php artisan serve
   ```

Customers can create accounts at `/register`. Admins create staff accounts from **Customers & staff**. Public registration never grants staff or administrator privileges. Customers select a preferred active staff member when requesting a drop-off appointment; the appointment records the preference but does not guarantee staff assignment.

Admins and staff can view and print monthly reports, including order-status counts and payments received. Customers can view their receipts but cannot print them from the system. Order-status changes create in-app updates; Semaphore SMS is sent only when staff marks an order ready for pickup. SMS requires a customer mobile number and a configured `SEMAPHORE_API_KEY`.
The shared login form offers an “I'm not a robot” checkbox that displays a four-second verification state. Customer accounts and unrecognized usernames must complete it; staff/admin accounts may continue without it. The checkbox and delay are a basic deterrent, not actual CAPTCHA or reliable human detection; use a CAPTCHA provider for stronger protection on a public production deployment.

Customers choose partial service (washed and dried, returned unfolded and without a bag) or full service (folded and bagged) when requesting an appointment. The base rate is ₱26/kg for partial service; full service adds ₱15/kg by default. Admins can adjust the full-service add-on and press-service rate in **Settings**. Appointment totals are estimates; the final total uses the actual drop-off weight, and each order saves its rates as a price snapshot.

### SMS and backups

SMS delivery uses Semaphore. Add `SEMAPHORE_API_KEY` to the server's `.env` file to enable texts; optionally set `SEMAPHORE_SENDER_NAME` if approved for the provider account. Keep provider credentials out of source control. Without a configured key or customer phone number, updates are still available in the in-app notification list. The admin settings page shows delivery results.

Database backup and recovery currently support SQLite. Admins can create and download backups in the private admin page. The console command `php artisan app:backup-laundry-database` also creates a backup. To restore, run `php artisan app:restore-laundry-database BACKUP_FILENAME`; the command prompts twice, creates a safety backup before replacement, and checks the selected database file.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
