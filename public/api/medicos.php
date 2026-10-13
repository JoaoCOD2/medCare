<?php
declare(strict_types=1);

// Carrega as configurações centrais e os helpers do projeto
require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Container;

// DECLARAÇÃO FORÇADA DA FUNÇÃO PARA EVITAR TRAVAMENTOS DE CACHE
if (!function_exists('exigir_metodo')) {
    function exigir_metodo(string $metodo): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($metodo)) {
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido.']);
            exit;
        }
    }
}

/** GET: Retorna a lista de médicos cadastrados no MySQL com limite */
exigir_metodo('GET');

$limite = (int) ($_GET['limite'] ?? 4);

$medicosRepo = Container::medicos();
$todosMedicos = $medicosRepo->listarTodos();
$medicosLimitados = array_slice($todosMedicos, 0, $limite);

// Limpa qualquer saída anterior e força a resposta em formato JSON nativo e puro
header('Content-Type: application/json');
echo json_encode([
    'sucesso' => true,
    'medicos' => $medicosLimitados
]);
exit;
