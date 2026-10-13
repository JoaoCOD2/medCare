<?php
declare(strict_types=1);

namespace MedCare\Repositories\Pdo;

use MedCare\Repositories\Contracts\HorarioRepositoryInterface;
use PDO;

class HorarioRepository implements HorarioRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarPorMedicoEData(int $medicoId, string $data): array
    {
        $sql = "SELECT id, medico_id, data_disponivel, hora_disponivel, ocupado 
                FROM horarios 
                WHERE medico_id = ? AND data_disponivel = ? 
                ORDER BY hora_disponivel ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$medicoId, $data]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarComoOcupado(int $id): bool
    {
        $sql = "UPDATE horarios SET ocupado = 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function liberarHorario(int $id): bool
    {
        $sql = "UPDATE horarios SET ocupado = 0 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
