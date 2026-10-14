<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Auth;
use MedCare\Core\Csrf;
use MedCare\Services\AgendamentoService;

/**
 * POST JSON:
 * {
 *   "medico_id": 1,
 *   "horario_id": 1,
 *   "tipo": "presencial"
 * }
 *
 * Cabeçalho obrigatório: X-CSRF-Token
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_resposta([
        'ok' => false,
        'erro' => 'Método não permitido.'
    ], 405);
}

if (!Csrf::validarRequisicao()) {
    json_resposta([
        'ok' => false,
        'erro' => 'Sessão expirada. Recarregue a página e tente novamente.'
    ], 419);
}

$corpo = json_decode(
    (string) file_get_contents('php://input'),
    true
);

if (!is_array($corpo)) {
    json_resposta([
        'ok' => false,
        'erro' => 'Requisição inválida.'
    ], 400);
}

$medicoId = filter_var(
    $corpo['medico_id'] ?? null,
    FILTER_VALIDATE_INT
);

$horarioId = trim((string) ($corpo['horario_id'] ?? ''));
$tipo = trim((string) ($corpo['tipo'] ?? ''));

if (
    $medicoId === false ||
    $medicoId === null ||
    $medicoId <= 0 ||
    $horarioId === '' ||
    $tipo === ''
) {
    json_resposta([
        'ok' => false,
        'erro' => 'Preencha médico, horário e tipo de atendimento.'
    ], 422);
}

if (!in_array($tipo, ['presencial', 'online'], true)) {
    json_resposta([
        'ok' => false,
        'erro' => 'Tipo de atendimento inválido.'
    ], 422);
}

try {
    $paciente = Auth::pacienteAtual();

    if (!$paciente || !isset($paciente['id'])) {
        json_resposta([
            'ok' => false,
            'erro' => 'Faça login como paciente para agendar uma consulta.'
        ], 401);
    }

    $consulta = (new AgendamentoService())->confirmar(
        (int) $paciente['id'],
        (int) $medicoId,
        $horarioId,
        $tipo
    );

    json_resposta([
        'ok' => true,
        'numero' => $consulta['numero'] ?? $consulta['numero_consulta'],
        'redirect' => url('pages/agendamento_sucesso.php', [
            'n' => $consulta['numero'] ?? $consulta['numero_consulta']
        ])
    ], 201);

} catch (DomainException $ex) {
    json_resposta([
        'ok' => false,
        'erro' => $ex->getMessage()
    ], 409);

} catch (Throwable $ex) {
    error_log(
        '[API Agendamento] ' .
        get_class($ex) . ': ' .
        $ex->getMessage()
    );

    json_resposta([
        'ok' => false,
        'erro' => 'Não foi possível confirmar a consulta. Tente novamente.'
    ], 500);
}