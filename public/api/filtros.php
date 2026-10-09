<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Container;

/** GET: listas usadas nos filtros e nas seções de especialidades. */
exigir_metodo('GET');
json_resposta([
    'ok'             => true,
    'especialidades' => Container::especialidades()->todas(),
    'cidades'        => Container::medicos()->cidades(),
]);
