<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Busca o paciente pelo e-mail. */
function buscar_paciente_por_email(string $email): ?array
{
    $configDb = require ROOT_PATH . '/config/database.php';

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $configDb['host'],
        $configDb['port'],
        $configDb['dbname'],
        $configDb['charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO(
        $dsn,
        $configDb['user'],
        $configDb['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    $stmt = $pdo->prepare(
        'SELECT id, nome, email, senha FROM pacientes WHERE email = :email LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $paciente = $stmt->fetch();

    return is_array($paciente) ? $paciente : null;
}

/** Aceita só caminhos internos (evita redirecionar para outro site). */
function destino_seguro(string $next): string
{
    if ($next !== '' && str_starts_with($next, '/') && !str_starts_with($next, '//')) {
        return $next;
    }
    return url('index.html');
}

$titulo = 'Entrar na Conta';
$erro   = '';
$email  = '';
$next   = (string) ($_GET['next'] ?? $_POST['next'] ?? '');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } else {
        try {
            $paciente = buscar_paciente_por_email($email);
            $hash     = (string) ($paciente['senha'] ?? '');

            if ($paciente && $hash !== '' && password_verify($senha, $hash)) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = [
                    'id'    => (int) $paciente['id'],
                    'nome'  => (string) $paciente['nome'],
                    'email' => (string) $paciente['email'],
                    'tipo'  => 'paciente',
                ];
                header('Location: ' . destino_seguro($next));
                exit;
            }

            $erro = 'E-mail ou senha incorretos.';
        } catch (Throwable $ex) {
            error_log('[MedCare Login] ' . $ex->getMessage());
            $erro = 'Não foi possível entrar agora. Tente novamente em instantes.';
        }
    }
}

include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="secao pt-4">
  <div class="container">
    <div class="auth-wrap">
      <div class="auth-card">
        <h1>Entrar</h1>
        <p class="text-muted-mc mb-4">Acesse sua conta para agendar e acompanhar consultas.</p>

        <?php if ($erro !== ''): ?>
          <div class="alert alert-danger" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('pages/login.php')) ?>" novalidate>
          <input type="hidden" name="next" value="<?= e($next) ?>">
          <div class="mb-3">
            <label class="form-label" for="email">E-mail</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
          </div>
          <div class="mb-4">
            <label class="form-label" for="senha">Senha</label>
            <input class="form-control" type="password" id="senha" name="senha" autocomplete="current-password" required>
          </div>
          <button class="btn btn-mc w-100 btn-lg" type="submit">Entrar</button>
        </form>

        <p class="text-center mt-4 mb-0">
          Não tem conta? <a href="<?= e(url('pages/cadastro.php')) ?>">Criar conta</a>
        </p>
      </div>
    </div>
  </div>
</section>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>