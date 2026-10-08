<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\{Auth, Container, Csrf};

/** POST JSON: {numero}  +  cabeçalho X-CSRF-Token */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_resposta(['ok' => false, 'erro' => 'Método não permitido.'], 405);
}
if (!Csrf::validarRequisicao()) {
    json_resposta(['ok' => false, 'erro' => 'Sessão expirada. Recarregue a página.'], 419);
}
$corpo = json_decode((string) file_get_contents('php://input'), true);
$numero = is_array($corpo) ? (string) ($corpo['numero'] ?? '') : '';

try {
    Container::consultas()->cancelar($numero, (int) Auth::pacienteAtual()['id']);
} catch (DomainException $ex) {
    json_resposta(['ok' => false, 'erro' => $ex->getMessage()], 422);
}
json_resposta(['ok' => true]);
