<?php

namespace Pengbin\CodeCoverage\Console;

use Illuminate\Console\Command;

class CodeCoverageConfigCommand extends Command
{
    protected $signature = 'code_coverage:config';

    protected $description = '发布配置文件';

    public function handle(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'code-coverage',
            '--force' => true
        ]);
    }
}