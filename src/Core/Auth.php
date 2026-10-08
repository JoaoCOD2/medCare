<?php
declare(strict_types=1);

namespace MedCare\Core;

/**
 * Autenticação e autorização.
 *
 * FASE 1: ainda não existe login real. pacienteAtual() devolve um paciente de
 * demonstração para o fluxo de agendamento funcionar de ponta a ponta.
 * FASE 4: troca-se pelo usuário vindo de $_SESSION['usuario'] (AuthService).
 * Os métodos exigir*() já definem o contrato que as páginas protegidas usam.
 */
final class Auth
{
    public static function pacienteAtual(): array
    {
        return $_SESSION['usuario'] ?? [
            'id'    => 1,
            'nome'  => 'João da Silva',
            'email' => 'joao.silva@exemplo.com',
            'tipo'  => 'paciente',
            'demo'  => true,
        ];
    }

    public static function logado(): bool
    {
        return isset($_SESSION['usuario']);
    }

    /** Fase 4: redireciona para o login se o perfil não bater. */
    public static function exigir(string $tipo): void
    {
        if (!self::logado() || ($_SESSION['usuario']['tipo'] ?? null) !== $tipo) {
            redirecionar(url('pages/login.php', ['next' => $_SERVER['REQUEST_URI'] ?? '']));
        }
    }
}
