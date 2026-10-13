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
use MedCare\Repositories\Pdo;
use RuntimeException;


final class Container
{
    private static array $cache = [];
    private static ?\PDO $pdoInstance = null;

    private static function driver(): string
    {
        // FORÇADO TEMPORARIAMENTE: Retorna 'demo' para o sistema rodar sem precisar do MySQL ligado
        return 'demo';
    }

    /**
     * Cria e gerencia uma única conexão com o banco de dados MySQL (Singleton)
     */
    private static function getPdoConnection(): \PDO
    {
        if (self::$pdoInstance === null) {
            $host     = config('db_host', '127.0.0.1');
            $port     = config('db_port', '3306');
            $dbname   = config('db_name', 'medcare');
            $username = config('db_user', 'root');
            $password = config('db_pass', '');

            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                self::$pdoInstance = new \PDO($dsn, $username, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (\PDOException $e) {
                throw new RuntimeException("Erro ao conectar com o banco de dados MySQL: " . $e->getMessage());
            }
        }

        return self::$pdoInstance;
    }

    public static function medicos(): MedicoRepositoryInterface
    {
        return self::$cache['medicos'] ??= match (self::driver()) {
            'demo'  => new Demo\MedicoRepository(),
            default => new Pdo\MedicoRepository(self::getPdoConnection()),
        };
    }

    public static function especialidades(): EspecialidadeRepositoryInterface
    {
        return self::$cache['especialidades'] ??= match (self::driver()) {
            'demo'  => new Demo\EspecialidadeRepository(),
            default => new Pdo\EspecialidadeRepository(self::getPdoConnection()),
        };
    }

    public static function horarios(): HorarioRepositoryInterface
    {
        return self::$cache['horarios'] ??= match (self::driver()) {
            'demo'  => new Demo\HorarioRepository(),
            default => new Pdo\HorarioRepository(self::getPdoConnection()),
        };
    }

    public static function consultas(): ConsultaRepositoryInterface
    {
        return self::$cache['consultas'] ??= match (self::driver()) {
            'demo'  => new Demo\ConsultaRepository(),
            default => new Pdo\ConsultaRepository(self::getPdoConnection()),
        };
    }

    /**
     * Gerencia o repositório de pacientes com suporte dinâmico ao modo demo
     */
    public static function pacientes(): mixed
    {
        return self::$cache['pacientes'] ??= match (self::driver()) {
            'demo'  => new Demo\PacienteRepository(),
            default => new Pdo\PacienteRepository(self::getPdoConnection()),
        };
    }
}
