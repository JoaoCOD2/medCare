    <?php

    declare(strict_types=1);

    require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

    use MedCare\Core\Auth;
    use MedCare\Core\Csrf;

    if (!function_exists('exigir_metodo')) {
        function exigir_metodo(string $metodo): void
        {
            if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($metodo)) {
                json_resposta(['ok' => false, 'erro' => 'Método não permitido.'], 405);
            }
        }
    }

    exigir_metodo('GET');

    // O estado de login nunca pode ficar em cache
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    $p = Auth::pacienteAtual();

    json_resposta([
        'ok'       => true,
        'nome'     => config('nome'),
        'csrf'     => Csrf::token(),
        'paciente' => $p ? [
            'id'    => $p['id'] ?? null,
            'nome'  => $p['nome'] ?? '',
            'email' => $p['email'] ?? '',
        ] : null,
    ]);