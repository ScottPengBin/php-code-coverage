<?php

return [

    'collect_path' => env('COLLECT_PATH', app_path('Services')),
    'report_data_path' => env('REPORT_DATA_PATH', public_path('code-coverage-report')),
];
