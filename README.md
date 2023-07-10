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

Configure the code_coverage path

```shell
return [

    'collect_path' => env('COLLECT_PATH', app_path('Services')),
    'report_data_path' => env('REPORT_DATA_PATH', public_path(config('app.code_coverage_report_path', 'code-coverage-report'))),
];

```

You can creates the config file (`code_coverage.php`)

