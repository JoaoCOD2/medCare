<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Services\BuscaService;

/**
 * GET /api/medicos.php
 * Filtros: q, especialidade, cidade, bairro, atendimento, data, limite
 */
exigir_metodo('GET');
$resultado = (new BuscaService())->buscar($_GET);
$medicos = $resultado['medicos'];
$limite = (int) ($_GET['limite'] ?? 0);
if ($limite > 0) {
    $medicos = array_slice($medicos, 0, $limite);
}
json_resposta([
    'ok'      => true,
    'total'   => count($resultado['medicos']),
    'filtros' => $resultado['filtros'],
    'medicos' => array_map(static fn($m) => medico_api($m), $medicos),
]);
