<?php
declare(strict_types=1);

namespace MedCare\Core;

/** Leitor mínimo de .env (sem dependências). */
final class Env
{
    private static array $vars = [];

    public static function carregar(string $arquivo): void
    {
        if (!is_file($arquivo)) {
            return;
        }
        foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
            $linha = trim($linha);
            if ($linha === '' || $linha[0] === '#' || !str_contains($linha, '=')) {
                continue;
            }
            [$chave, $valor] = explode('=', $linha, 2);
            self::$vars[trim($chave)] = trim($valor, " \t\"'");
        }
    }

    public static function get(string $chave, ?string $padrao = null): ?string
    {
        if (isset(self::$vars[$chave])) {
            return self::$vars[$chave];
        }
        $env = getenv($chave);
        return $env !== false ? $env : $padrao;
    }
}
