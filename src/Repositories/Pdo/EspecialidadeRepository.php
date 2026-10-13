<?php
namespace MedCare\Repositories\Pdo;

use MedCare\Repositories\Contracts\EspecialidadeRepositoryInterface;
use PDO;

class EspecialidadeRepository implements EspecialidadeRepositoryInterface
{
    private PDO $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarTodas(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM especialidades ORDER BY nome ASC"
        );

        $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mapaIcones = [
            'cardiologia'    => 'fa-heart-pulse',
            'dermatologia'   => 'fa-droplet',
            'pediatria'      => 'fa-baby',
            'ortopedia'      => 'fa-bone',
            'ginecologia'    => 'fa-venus',
            'oftalmologia'   => 'fa-eye',
            'neurologia'     => 'fa-brain',
            'psiquiatria'    => 'fa-comments',
            'clinica geral'  => 'fa-stethoscope',
            'clínica geral'  => 'fa-stethoscope',
            'endocrinologia' => 'fa-vial',
            'nutricao'       => 'fa-apple-whole',
            'nutrição'       => 'fa-apple-whole',
            'odontologia'    => 'fa-tooth',
        ];

        return array_map(
            static function (array $e) use ($mapaIcones): array {
                $nomeLimpo = mb_strtolower(trim($e['nome'] ?? ''));

                $iconeBanco = trim($e['icone'] ?? '');

                $classeIcone = $mapaIcones[$nomeLimpo]
                    ?? $iconeBanco
                    ?: 'fa-user-doctor';

                // Retira fa-solid caso já esteja no valor recebido.
                $classeIcone = preg_replace(
                    '/\bfa-solid\b/',
                    '',
                    $classeIcone
                ) ?? $classeIcone;

                $classeIcone = trim($classeIcone);

                // O home.js acrescenta "fa-solid ".
                $e['icone'] = $classeIcone;
                $e['class_icone'] = 'fa-solid ' . $classeIcone;
                $e['slug'] = $e['slug'] ?? $nomeLimpo;

                return $e;
            },
            $especialidades
        );
    }

    public function todas(): array
    {
        return $this->listarTodas();
    }

    public function porId(int $id): ?array
    {
        foreach ($this->listarTodas() as $especialidade) {
            if ((int) $especialidade['id'] === $id) {
                return $especialidade;
            }
        }

        return null;
    }
}
