<?php
declare(strict_types=1);

namespace MedCare\Core;

/** Token CSRF por sessão. Obrigatório em todo POST. */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function validar(?string $recebido): bool
    {
        return is_string($recebido) && $recebido !== ''
            && hash_equals(self::token(), $recebido);
    }

    /** Aceita o token no cabeçalho X-CSRF-Token (fetch) ou no campo _csrf (formulário). */
    public static function validarRequisicao(): bool
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? null);
        return self::validar($token);
    }
}
