<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Container;

/**
 * GET /api/horarios.php?medico_id=1            -> dias com agenda
 * GET /api/horarios.php?medico_id=1&data=Y-m-d -> slots do dia
 */
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_resposta(['ok' => false, 'erro' => 'Método não permitido.'], 405);
}
$medicoId = (int) ($_GET['medico_id'] ?? 0);
if ($medicoId <= 0 || !Container::medicos()->porId($medicoId)) {
    json_resposta(['ok' => false, 'erro' => 'Médico não encontrado.'], 404);
}

$data = (string) ($_GET['data'] ?? '');
if ($data === '') {
    $dias = array_map(static fn($d) => $d + [
        'rotulo' => dia_semana_curto($d['data']),
        'dia'    => date('d', strtotime($d['data'])),
        'mes'    => date('m', strtotime($d['data'])),
    ], Container::horarios()->dias($medicoId, 10));
    json_resposta(['ok' => true, 'dias' => $dias]);
}

$slots = array_map(static fn($s) => [
    'id'     => $s['id'],
    'hora'   => hora_curta($s['hora_inicio']),
    'status' => $s['status'],
], Container::horarios()->doDia($medicoId, $data));
json_resposta(['ok' => true, 'data' => $data, 'slots' => $slots]);
