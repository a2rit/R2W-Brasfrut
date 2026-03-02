<?php
/**
 * Created by PhpStorm.
 * User: Litiano
 * Date: 05/07/2017
 * Time: 15:27
 */

use Litiano\Sap\Enum\BoSuppLangs;

return [
    'username' => env('SAP_USERNAME', 'manager'),
    'password' => env('SAP_PASSWORD'),
    'server' => env('SAP_SERVER_ADDRESS'),
    'license_server' => env('SAP_LICENSE_SERVER'),
    'language' => env('SAP_LANGUAGE', BoSuppLangs::ln_Portuguese_Br),
    'use_trusted' => env('SAP_USE_TRUSTED', false),
    'sld_server' => env('SAP_SLD_SERVER'),

    'db' => [
        'driver' => env('SAP_DB_DRIVER', 'sqlsrv'),
        'host' => env('SAP_DB_HOST', 'localhost'),
        'port' => env('SAP_DB_PORT', '1433'),
        'database' => env('SAP_DB_DATABASE'),
        'username' => env('SAP_DB_USERNAME', 'sa'),
        'password' => env('SAP_DB_PASSWORD'),
        'charset' => 'utf8',
        'prefix' => '',
        'type' => env('SAP_DB_TYPE'),
        'odbc' => (bool)env('SAP_USE_ODBC_DRIVER', false),
        'odbc_datasource_name' => env('SAP_ODBC_DATA_SOURCE'),
    ],

    (bool)env('SAP_DEBUG', false)
];