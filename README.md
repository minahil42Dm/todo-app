# To-Do

A small to-do application built on the official Laravel Livewire starter kit. Signed-in users can create and manage their own tasks. Each person only sees the tasks that belong to them.

The task pages are a Laravel controller and Blade views. Registration, login, and email verification come from Laravel Fortify, with the settings screens built as Livewire components.

## Features

### Accounts

- Users can register and log in with an email address and password.
- Email verification is turned on. The dashboard and every task page require a verified email address.
- A signed-in user can resend the verification message from the profile settings page.
- The starter kit also includes password reset, two-factor authentication, passkeys, and profile, security, and appearance settings.

The example environment file sets `MAIL_MAILER=log`. With that setting, Laravel writes the verification message to `storage/logs/laravel.log` instead of delivering it to an inbox. Sending real email requires a mailer configured with the `MAIL_*` variables. Do not put passwords or API keys in this README or in Git.

### Tasks

Signed-in, verified users can:

- Create a task with a title, an optional description, an optional due date, and a completed checkbox.
- View one task, edit it, and delete it.
- Mark a task complete or incomplete from the list or the task page.
- Filter the list with All, Active, and Completed.

`all` shows every task owned by the current user. `active` shows tasks that are not completed. `completed` shows tasks that are completed. If `status` is missing or not one of those three values, the list uses `all`.

A task stores:

| Field | Rule |
| --- | --- |
| Title | Required text, up to 255 characters |
| Description | Optional text |
| Due date | Optional date |
| Completed | Boolean. A missing value is stored as not completed |

The application sets `user_id` from the signed-in user. A value submitted in the form is ignored.

### Access restrictions

- Guests who open a task page are redirected to the login page.
- The task list, task page, edit page, update action, and delete action only work for the task owner.
- Another user's task is treated as missing, so its title and details are not shown.

## Technology stack

The Required column comes from `composer.json` and `package.json`. The Installed column comes from `composer.lock`, `package-lock.json`, or the PHP version on this machine.

| Software | Required | Installed |
| --- | --- | --- |
| PHP | `^8.3` | 8.5.12 |
| Laravel | `^13.17` | 13.42.0 |
| Laravel Fortify | `^1.37.2` | 1.42.0 |
| Livewire | `^4.1` | 4.4.8 |
| Flux | `^2.13.1` | 2.21.0 |
| Blaze | `^1.0` | 1.0.21 |
| Tailwind CSS | `^4.0.7` | 4.3.5 |
| Vite Plus | `1.4.0` | 1.4.0 |
| PHPUnit | `^12.5.23` | 12.5.43 |

`^8.3` means PHP 8.3 or a newer PHP 8 release, and not PHP 9. Views use Blade. Tests use PHPUnit. The frontend scripts in `package.json` are `vp dev` and `vp build` from Vite Plus 1.4.0. The `vite` package is pinned to `@voidzero-dev/vite-plus-core` 1.4.0.

## Prerequisites

- PHP 8.3 or a newer PHP 8 release (`^8.3`)
- Composer
- The PHP `pdo_mysql` extension
- Node.js and npm
- A running MySQL server when you choose the MySQL settings below

## Installation and setup

Clone the repository and move into the project folder:

```bash
git clone https://github.com/minahil42Dm/todo-app.git
cd todo-app
```

The `setup` script in `composer.json` runs `composer install`, copies `.env.example` to `.env` when `.env` is missing, generates the application key, runs `php artisan migrate --force`, installs npm packages, and runs `npm run build`. That script migrates immediately after the copy, while `.env.example` still says `DB_CONNECTION=sqlite`.

To use MySQL, run the same commands yourself and edit `.env` before the migration:

```bash
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
```

Create an empty MySQL database, then set these values in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

`127.0.0.1` and `3306` are the MySQL defaults in `config/database.php`. Replace the database name, username, and password with your own. Keep `.env` private. It is listed in `.gitignore`.

`APP_URL` in `.env.example` is `http://localhost:8000`. Leave it matching the address you use to open the app.

Generate the key, migrate, install the frontend packages, and build the assets. These are the remaining `setup` script commands. Start MySQL before the migration. Migrations create the users, sessions, cache, jobs, passkeys, and tasks tables. The example environment stores sessions, cache, and queues in the database, so log in only after migrate has finished.

```bash
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

Start the development processes with the `dev` script:

```bash
composer run dev
```

The `dev` script in `composer.json` runs these two commands, in order:

- `Composer\Config::disableProcessTimeout`
- `@php artisan dev`

`Composer\Config::disableProcessTimeout` turns off Composer's process timeout so the development processes can keep running. `@php artisan dev` then starts:

- `php artisan serve`
- `php artisan queue:listen --tries=1 --timeout=0`
- `npm run dev`

`npm run dev` runs `vp dev`. Open `http://localhost:8000`.

## Automated tests

Run the PHPUnit suite with:

```bash
php artisan test
```

`phpunit.xml` points the tests at a SQLite `:memory:` database, so the test run does not use your MySQL database. Feature tests live in `tests/Feature`, including `tests/Feature/TaskTest.php` for the task list, ownership rules, filters, validation, and guest access.

The Composer `test` script is different. It clears the config cache, checks formatting with Pint, runs PHPStan, and then runs `php artisan test`.

## Learning outcomes

Working through this project shows how:

- A route sends a request to a controller, and the controller returns a Blade view.
- Fortify registers a user, logs that user in, and requires a verified email address before the dashboard and task pages open.
- Validation rejects a missing title.
- Validation rejects a title longer than 255 characters.
- Validation rejects a due date that is not a date.
- Validation rejects a completed value that is not boolean.
- The `ownedBy` query scope limits task queries to the signed-in user. Route model binding applies that same scope, so a request for another user's task is not found.
- The All, Active, and Completed filters change one task list. A missing or invalid status falls back to All.
- `php artisan test` checks those rules with PHPUnit and a SQLite memory database, separate from the development database.
