<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';

use Illuminate\Database\Capsule\Manager as DB;

return [
    'migration_base_class' => App\Database\Migration::class,
    'paths' => [
        'migrations' => 'db/migrations'
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'default',
        'default' => [
            'name' => DB::connection()->getDatabaseName(),
            'connection' => DB::connection()->getPdo()
        ]
    ]
];
