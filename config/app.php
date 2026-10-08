<?php
declare(strict_types=1);

use MedCare\Core\Env;

Env::carregar(dirname(__DIR__) . '/.env');

return [
    'nome'        => Env::get('APP_NAME', 'MedCare'),
    'ambiente'    => Env::get('APP_ENV', 'local'),
    'base_url'    => rtrim((string) Env::get('APP_BASE_URL', ''), '/'),
    // "demo" usa src/Repositories/Demo; "pdo" usa src/Repositories/Pdo (MySQL).
    'data_driver' => Env::get('DATA_DRIVER', 'demo'),
    'fuso'        => 'America/Sao_Paulo',
    'contato'     => [
        'telefone' => '(51) 3000-0000',
        'email'    => 'contato@medcare.example',
        'horario'  => 'Seg a Sex, 8h às 18h',
    ],
];
