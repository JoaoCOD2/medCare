<?php
require __DIR__ . '/src/bootstrap.php';

try {
    $medicos = MedCare\Core\Container::medicos();
    $horarios = MedCare\Core\Container::horarios();
    $consultas = MedCare\Core\Container::consultas();

    echo "Driver: " . config('data_driver', 'demo') . PHP_EOL;
    echo "Classe medicos: " . get_class($medicos) . PHP_EOL;
    echo "Classe horarios: " . get_class($horarios) . PHP_EOL;
    echo "Classe consultas: " . get_class($consultas) . PHP_EOL;
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . PHP_EOL;
}
