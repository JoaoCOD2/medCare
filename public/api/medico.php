<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Container;

/** GET /api/medico.php?id=1 -> perfil completo, avaliações e próximos dias. */
exigir_metodo('GET');
$id = (int) ($_GET['id'] ?? 0);
$m = $id > 0 ? Container::medicos()->porId($id) : null;
if (!$m) {
    json_resposta(['ok' => false, 'erro' => 'Médico não encontrado.'], 404);
}
json_resposta([
    'ok'         => true,
    'medico'     => medico_api($m, true),
    'avaliacoes' => Container::medicos()->avaliacoes($id),
    'dias'       => Container::horarios()->dias($id, 5),
]);
