<?php

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'dbname' => getenv('DB_DATABASE') ?: 'gmleads_db',
        'port' => getenv('DB_PORT') ?: '3306',
        'charset' => 'utf8mb4',
    ],
    'username' => getenv('DB_USERNAME') ?: 'admin',
    'password' => getenv('DB_PASSWORD') ?: 'secret',
];
