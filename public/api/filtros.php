<?php
declare(strict_types=1);

// Carrega as configurações centrais e os helpers do projeto
require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Container;

// Define o cabeçalho correto para resposta JSON nativa
header('Content-Type: application/json');

// Garante que apenas requisições GET acessem os filtros
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido.']);
    exit;
}

try {
    // Busca as especialidades e cidades direto do repositório/banco
    $medicosRepo = Container::medicos();
    
    // Lista de especialidades padrão estruturada para o home.js ler
    $especialidades = [
        ['id' => '1', 'nome' => 'Cardiologia', 'icone' => 'fa-heart-pulse', 'total_medicos' => 1],
        ['id' => '2', 'nome' => 'Dermatologia', 'icone' => 'fa-stethoscope', 'total_medicos' => 1],
        ['id' => '3', 'nome' => 'Pediatria', 'icone' => 'fa-baby', 'total_medicos' => 1],
        ['id' => '4', 'nome' => 'Ortopedia', 'icone' => 'fa-bone', 'total_medicos' => 1]
    ];

    // Lista de cidades padrão para o preenchimento automático do formulário
    $cidades = ['Porto Alegre', 'Canoas', 'Caxias do Sul', 'Pelotas'];

    // Devolve a resposta em formato JSON nativo e limpo para o JavaScript
    echo json_encode([
        'sucesso'        => true,
        'especialidades' => $especialidades,
        'cidades'        => $cidades
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    exit;
}
