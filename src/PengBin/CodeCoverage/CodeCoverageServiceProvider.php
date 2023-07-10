<?php

namespace PengBin\CodeCoverage;

use Illuminate\Support\ServiceProvider;

class CodeCoverageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../../config/code_coverage.php' => config_path('code_coverage'),
        ],'code-coverage');
    }
}