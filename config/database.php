<?php
declare(strict_types=1);

use MedCare\Core\Env;

// Usado apenas quando DATA_DRIVER=pdo (Fase 3). Credenciais vêm do .env.
return [
    'host'    => Env::get('DB_HOST', '127.0.0.1'),
    'port'    => (int) Env::get('DB_PORT', '3306'),
    'dbname'  => Env::get('DB_NAME', 'medcare'),
    'user'    => Env::get('DB_USER', 'root'),
    'pass'    => Env::get('DB_PASS', ''),
    'charset' => 'utf8mb4',
];
