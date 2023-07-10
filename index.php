<?php

$envPath = __DIR__ . '/../../../.env';

$xdebugExtension = extension_loaded('xdebug');
if (!$xdebugExtension) {
    return false;
}

//读取.env中的配置
$startCodeCoverage = false;
$projectRootPath = '';
if (is_file($envPath)) {
    $envs = file($envPath);
    foreach ($envs as $env) {
        if (str_starts_with($env, 'START_CODE_COVERAGE=')) {
            $startCodeCoverage = boolval(trim((explode('=', $env)[1])) ?? false);
            if (!$startCodeCoverage) {
                break;
            }
            continue;
        }
        if (str_starts_with($env, 'PROJECT_ROOT_PATH=')) {
            $projectRootPath = trim((explode('=', $env)[1]) ?? '/home/www-data/pms');
        }

        if ($startCodeCoverage && !empty($projectRootPath)) {
            break;
        }
    }
}

if ($startCodeCoverage && !empty($projectRootPath)) {
    xdebug_start_code_coverage(XDEBUG_CC_UNUSED);
    xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_INCLUDE, [$projectRootPath . '/app/Services/']);

    register_shutdown_function(function () {
        $coverageData = xdebug_get_code_coverage();
        xdebug_stop_code_coverage(true);
        Pengbin\CodeCoverage\Jobs\CodeCoverageCollectJob::dispatch($coverageData);
    });
}

