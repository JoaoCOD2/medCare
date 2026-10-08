<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\{Auth, Csrf};
use MedCare\Services\AgendamentoService;

/** POST JSON: {medico_id, horario_id, tipo}  +  cabeçalho X-CSRF-Token */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_resposta(['ok' => false, 'erro' => 'Método não permitido.'], 405);
}
if (!Csrf::validarRequisicao()) {
    json_resposta(['ok' => false, 'erro' => 'Sessão expirada. Recarregue a página e tente novamente.'], 419);
}

$corpo = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($corpo)) {
    json_resposta(['ok' => false, 'erro' => 'Requisição inválida.'], 400);
}

$medicoId  = (int) ($corpo['medico_id'] ?? 0);
$horarioId = (string) ($corpo['horario_id'] ?? '');
$tipo      = (string) ($corpo['tipo'] ?? '');
if ($medicoId <= 0 || $horarioId === '' || $tipo === '') {
    json_resposta(['ok' => false, 'erro' => 'Preencha médico, horário e tipo de atendimento.'], 422);
}

try {
    $paciente = Auth::pacienteAtual(); // Fase 4: Auth::exigir('paciente')
    $consulta = (new AgendamentoService())->confirmar((int) $paciente['id'], $medicoId, $horarioId, $tipo);
} catch (DomainException $ex) {
    json_resposta(['ok' => false, 'erro' => $ex->getMessage()], 409);
}

json_resposta([
    'ok'       => true,
    'numero'   => $consulta['numero'],
    'redirect' => url('pages/agendamento_sucesso.php', ['n' => $consulta['numero']]),
], 201);
