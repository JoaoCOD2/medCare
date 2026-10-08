<?php
declare(strict_types=1);

namespace MedCare\Core;

use MedCare\Repositories\Contracts\{
    ConsultaRepositoryInterface,
    EspecialidadeRepositoryInterface,
    HorarioRepositoryInterface,
    MedicoRepositoryInterface
};
use MedCare\Repositories\Demo;
use RuntimeException;

/**
 * Único lugar que decide QUAL implementação de repositório usar.
 *
 * Para migrar ao MySQL: crie as classes em src/Repositories/Pdo/ (mesmos
 * contratos de Contracts/), troque os casos "pdo" abaixo e defina
 * DATA_DRIVER=pdo no .env. Páginas e serviços não mudam.
 */
final class Container
{
    private static array $cache = [];

    private static function driver(): string
    {
        return (string) config('data_driver', 'demo');
    }

    private static function pdoIndisponivel(string $nome): never
    {
        throw new RuntimeException("Repositório PDO '{$nome}' ainda não implementado (Fase 3). Use DATA_DRIVER=demo.");
    }

    public static function medicos(): MedicoRepositoryInterface
    {
        return self::$cache['medicos'] ??= match (self::driver()) {
            'demo'  => new Demo\MedicoRepository(),
            default => self::pdoIndisponivel('MedicoRepository'),
        };
    }

    public static function especialidades(): EspecialidadeRepositoryInterface
    {
        return self::$cache['especialidades'] ??= match (self::driver()) {
            'demo'  => new Demo\EspecialidadeRepository(),
            default => self::pdoIndisponivel('EspecialidadeRepository'),
        };
    }

    public static function horarios(): HorarioRepositoryInterface
    {
        return self::$cache['horarios'] ??= match (self::driver()) {
            'demo'  => new Demo\HorarioRepository(),
            default => self::pdoIndisponivel('HorarioRepository'),
        };
    }

    public static function consultas(): ConsultaRepositoryInterface
    {
        return self::$cache['consultas'] ??= match (self::driver()) {
            'demo'  => new Demo\ConsultaRepository(),
            default => self::pdoIndisponivel('ConsultaRepository'),
        };
    }
}
