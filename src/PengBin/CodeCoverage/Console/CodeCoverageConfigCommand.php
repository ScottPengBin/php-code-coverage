<?php

namespace Pengbin\CodeCoverage\Console;

use Illuminate\Console\Command;

class CodeCoverageConfigCommand extends Command
{
    protected $name = 'code_coverage:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the web side code coverage profile';


    public function handle()
    {
        $this->call('vendor:publish', [
                '--tag' => 'code-coverage',
                '--force' => true
            ]
        );
    }
}