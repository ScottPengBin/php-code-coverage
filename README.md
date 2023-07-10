# Web Code Coverage for Laravel

---

## Getting Started

The installation step below work on the latest versions of the Laravel framework (8.x and 9.x).

### Install

Install the `pengbin/codecoverage` package:

```bash
composer require pengbin/codecoverage
```

Enable capturing unhandled exception to report to Sentry by making the following change to your `App/Exceptions/Handler.php`:

### Configure

Configure the Sentry DSN with this command:

```shell
php artisan code_coverage:config
```

It creates the config file (`config/code_coverage.php`)

