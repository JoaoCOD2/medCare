<?php
declare(strict_types=1);

/**
 * Login de pacientes — MedCare
 * Arquivo: public/api/login.php
 */

ob_start();

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    function responderLogin(int $status, array $dados): never
    {
        http_response_code($status);

        if (ob_get_level() > 0) {
            ob_clean();
        }

        echo json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');

        responderLogin(405, [
            'sucesso' => false,
            'erro' => 'Método não permitido.'
        ]);
    }

    $email = trim((string) ($_POST['login_email'] ?? ''));
    $senha = (string) ($_POST['login_senha'] ?? '');

    if ($email === '' || $senha === '') {
        responderLogin(400, [
            'sucesso' => false,
            'erro' => 'Preencha o e-mail e a senha.'
        ]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responderLogin(400, [
            'sucesso' => false,
            'erro' => 'Informe um endereço de e-mail válido.'
        ]);
    }

    try {
        $configDb = require ROOT_PATH . '/config/database.php';

        if (!is_array($configDb)) {
            throw new RuntimeException(
                'A configuração do banco de dados é inválida.'
            );
        }

        foreach (['host', 'port', 'dbname', 'user', 'pass'] as $chave) {
            if (!array_key_exists($chave, $configDb)) {
                throw new RuntimeException(
                    'Configuração ausente no banco: ' . $chave
                );
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $configDb['host'],
            $configDb['port'],
            $configDb['dbname']
        );

        $pdo = new PDO(
            $dsn,
            $configDb['user'],
            $configDb['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $sql = '
            SELECT id, nome, email, senha
            FROM pacientes
            WHERE email = :email
            LIMIT 1
        ';

    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $paciente = $stmt->fetch();




        $paciente = $stmt->fetch();

        if (
            !$paciente ||
            !isset($paciente['senha']) ||
            !password_verify($senha, (string) $paciente['senha'])
        ) {
            responderLogin(401, [
                'sucesso' => false,
                'erro' => 'E-mail ou senha incorretos.'
            ]);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException(
                'A sessão não foi inicializada corretamente.'
            );
        }

        if (!session_regenerate_id(true)) {
            throw new RuntimeException(
                'Não foi possível renovar a sessão.'
            );
        }

        $_SESSION['usuario_id'] = (int) $paciente['id'];
        $_SESSION['usuario_nome'] = (string) $paciente['nome'];
        $_SESSION['usuario_tipo'] = 'paciente';

        responderLogin(200, [
            'sucesso' => true,
            'mensagem' => 'Login realizado com sucesso!',
            'redirecionar' => 'pages/consultas.php'
        ]);

    } catch (Throwable $e) {
        error_log(
            '[MedCare Login] ' .
            get_class($e) . ': ' .
            $e->getMessage()
        );

        responderLogin(500, [
            'sucesso' => false,
            'erro' => 'Não foi possível realizar o login. Verifique a configuração do servidor.'
        ]);
    }
