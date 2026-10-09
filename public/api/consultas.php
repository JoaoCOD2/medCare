<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\{Auth, Container};

/**
 * GET /api/consultas.php               -> consultas do paciente
 * GET /api/consultas.php?numero=MC-... -> uma consulta
 */
exigir_metodo('GET');
$pacienteId = (int) Auth::pacienteAtual()['id'];   // Fase 4: Auth::exigir('paciente')
$medicos = Container::medicos();

$montar = static function (array $c) use ($medicos): array {
    $m = $medicos->porId((int) $c['medico_id']);
    return $c + [
        'cancelavel' => in_array($c['status'], ['confirmada', 'aguardando'], true),
        'medico'     => $m ? medico_api($m) : null,
    ];
};

if (isset($_GET['numero'])) {
    $c = Container::consultas()->porNumero((string) $_GET['numero'], $pacienteId);
    if (!$c) {
        json_resposta(['ok' => false, 'erro' => 'Consulta não encontrada.'], 404);
    }
    json_resposta(['ok' => true, 'consulta' => $montar($c)]);
}

json_resposta([
    'ok'        => true,
    'consultas' => array_map($montar, Container::consultas()->doPaciente($pacienteId)),
]);
