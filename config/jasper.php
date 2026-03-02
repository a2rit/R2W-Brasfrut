<?php

return [
    'sap' => [
        'driver'      => 'generic',
        'host'        => env('SAP_DB_HOST'),
        'port'        => env('SAP_SQL_PORT'),
        'username'    => env('SAP_DB_USER'),
        'password'    => env('SAP_DB_PASSWORD'),
        'database'    => env('SAP_DB_NAME'),
        'jdbc_driver' => env('JDBC_DRIVER'),
        'jdbc_url'    => env('JDBC_URL').''.env('SAP_DB_DATABASE'),
        'jdbc_dir'    => base_path() . '/vendor/cossou/jasperphp/src/JasperStarter/jdbc/'
    ],
    'r2w' => [
        'driver'      => 'generic',
        'host'        => env('DB_HOST'),
        'port'        => env('DB_PORT'),
        'username'    => env('DB_USERNAME'),
        'password'    => env('DB_PASSWORD'),
        'database'    => env('DB_DATABASE'),
        'jdbc_driver' => env('JDBC_DRIVER'),
        'jdbc_url'    => env('JDBC_URL').''.env('DB_DATABASE'),
        'jdbc_dir'    => base_path() . '/vendor/cossou/jasperphp/src/JasperStarter/jdbc/'
    ]
];
?>