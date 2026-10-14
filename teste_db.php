<?php
require __DIR__ . '/src/bootstrap.php';

$config = require __DIR__ . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . $config['host']
        . ";port=" . $config['port']
        . ";dbname=" . $config['dbname']
        . ";charset=" . $config['charset'],
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Ligacao MySQL OK" . PHP_EOL;
    echo "Base de dados: "
        . $pdo->query('SELECT DATABASE()')->fetchColumn()
        . PHP_EOL;
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . PHP_EOL;
}
