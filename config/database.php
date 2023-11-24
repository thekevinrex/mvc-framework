<?php 

    return [

        'default' => env('DB_CONNECTION', 'mysql'),

        'connections' => [

            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'post' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'mvc_framework'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),

                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],

        ]
    ];
?>