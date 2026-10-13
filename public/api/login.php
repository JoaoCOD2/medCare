<?php declare(strict_types=1);

/**
 * Endpoint de Login de Pacientes - MedCare
 */

ob_start();

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Env;

Env::carregar(ROOT_PATH . '/.env');

ob_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'erro' => 'Método de requisição não permitido.']);
    exit;
}

$email = trim($_POST['login_email'] ?? '');
$senha = $_POST['login_senha'] ?? '';

if (empty($email) || empty($senha)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'E-mail e senha devem ser preenchidos.']);
    exit;
}

try {
    $configDb = require ROOT_PATH . '/config/database.php';
    
    $dsn = "mysql:host={$configDb['host']};port={$configDb['port']};dbname={$configDb['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $configDb['user'], $configDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->prepare("SELECT id, nome, senha FROM pacientes WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $paciente = $stmt->fetch();

    if (!$paciente || !password_verify($senha, $paciente['senha'])) {
        http_response_code(401);
        echo json_encode(['sucesso' => false, 'erro' => 'E-mail ou senha incorretos.']);
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['usuario_id'] = $paciente['id'];
    $_SESSION['usuario_nome'] = $paciente['nome'];
    $_SESSION['usuario_tipo'] = 'paciente';

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Login realizado com sucesso! Seja bem-vindo, ' . $paciente['nome'] . '.'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno no servidor ao tentar logar: ' . $e->getMessage()]);
    exit;
}
