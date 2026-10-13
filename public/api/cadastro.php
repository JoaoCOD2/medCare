<?php declare(strict_types=1);

/**
 * Endpoint de Cadastro de Pacientes - MedCare
 */

// 1. Inicializa o ambiente usando o bootstrap central do projeto
require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Env;

// Garante o carregamento do .env
Env::carregar(ROOT_PATH . '/.env');

// Define o cabeçalho para resposta JSON
header('Content-Type: application/json');

// 2. Bloqueia se não for uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'erro' => 'Método de requisição não permitido.']);
    exit;
}

// 4. Coleta os dados usando os novos nomes mascarados do formulário
$nome = trim($_POST['mc_n'] ?? '');
$cpf = trim($_POST['mc_c'] ?? '');
$nascimento = trim($_POST['mc_d'] ?? '');
$email = trim($_POST['mc_e'] ?? '');
$telefone = trim($_POST['mc_t'] ?? '');
$senha = $_POST['mc_s'] ?? '';
$senha2 = $_POST['mc_s2'] ?? '';
$termos = $_POST['termos'] ?? null;

// 5. Validação dos campos obrigatórios no Servidor
if (empty($nome) || empty($cpf) || empty($nascimento) || empty($email) || empty($senha) || empty($senha2)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Todos os campos obrigatórios devem ser preenchidos.']);
    exit;
}

if (!$termos) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Você precisa aceitar os termos de uso.']);
    exit;
}

if ($senha !== $senha2) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'As senhas digitadas não coincidem.']);
    exit;
}

// 6. Conexão com o banco de dados via PDO
try {
    $configDb = require ROOT_PATH . '/config/database.php';
    
    $dsn = "mysql:host={$configDb['host']};port={$configDb['port']};dbname={$configDb['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $configDb['user'], $configDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 7. Verifica se o CPF ou E-mail já existem na tabela pacientes
    $stmtCheck = $pdo->prepare("SELECT id, cpf, email FROM pacientes WHERE cpf = ? OR email = ? LIMIT 1");
    $stmtCheck->execute([$cpf, $email]);
    $usuarioExistente = $stmtCheck->fetch();

    if ($usuarioExistente) {
        http_response_code(400);
        if ($usuarioExistente['cpf'] === $cpf) {
            echo json_encode(['sucesso' => false, 'erro' => 'Este CPF já está cadastrado no sistema.']);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Este e-mail já está cadastrado no sistema.']);
        }
        exit;
    }

    // 8. Criptografa a senha com segurança
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

    // 9. Insere o novo paciente no banco de dados
    $sqlInsert = "INSERT INTO pacientes (nome, cpf, nascimento, email, telefone, senha) 
                  VALUES (:nome, :cpf, :nascimento, :email, :telefone, :senha)";
                  
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        ':nome' => $nome,
        ':cpf' => $cpf,
        ':nascimento' => $nascimento,
        ':email' => $email,
        ':telefone' => !empty($telefone) ? $telefone : null,
        ':senha' => $senhaHash
    ]);

    // Cadastro realizado com sucesso!
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Cadastro realizado com sucesso! Redirecionando...'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno no servidor ao salvar os dados: ' . $e->getMessage()]);
    exit;
}
