<?php
declare(strict_types=1);

namespace MedCare\Core;

final class Session
{
    /** Tempo máximo sem atividade (em segundos). 1800 = 30 minutos. */
    private const INATIVIDADE = 1800;

    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) == 443);

        // Só aceita sessão via cookie e ignora IDs de sessão inventados
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.gc_maxlifetime', (string) self::INATIVIDADE);

        session_set_cookie_params([
            'lifetime' => 0,          // cookie de sessão: some ao fechar o navegador
            'path'     => '/',
            'domain'   => '',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('medcare_sid');   // mesmo nome de sessão que o projeto já usava
        session_start();

        // Expira a sessão após o tempo máximo sem atividade
        $agora = time();
        if (isset($_SESSION['_ultimo_acesso']) && ($agora - (int) $_SESSION['_ultimo_acesso']) > self::INATIVIDADE) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['_ultimo_acesso'] = $agora;
    }

    /** Encerra a sessão por completo (use no logout). */
    public static function destruir(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $c = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $c['path'],
                'domain'   => $c['domain'],
                'secure'   => $c['secure'],
                'httponly' => $c['httponly'],
                'samesite' => $c['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }
}