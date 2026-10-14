    <?php
    declare(strict_types=1);

    namespace MedCare\Core;

    final class Auth
    {
        public static function pacienteAtual(): ?array
        {
            $u = $_SESSION['usuario'] ?? null;

            if (is_array($u) && ($u['tipo'] ?? null) === 'paciente') {
                return $u;
            }

            return null;
        }

        public static function logado(): bool
        {
            return self::pacienteAtual() !== null;
        }

        public static function exigir(string $tipo): void
        {
            if (!self::logado() || ($_SESSION['usuario']['tipo'] ?? null) !== $tipo) {
                redirecionar(url('pages/login.php', ['next' => $_SERVER['REQUEST_URI'] ?? '']));
            }
        }
    }