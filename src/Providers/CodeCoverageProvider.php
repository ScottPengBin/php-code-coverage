<?php

namespace Pengbin\CodeCoverage\Providers;

use Illuminate\Support\ServiceProvider;
use Pengbin\CodeCoverage\Console\CodeCoverageReportCommand;

class CodeCoverageProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CodeCoverageReportCommand::class
            ]);
        }
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/code_coverage.php' => config_path('code_coverage.php'),
        ], 'code-coverage');
    }
}
