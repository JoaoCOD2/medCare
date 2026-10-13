<?php
declare(strict_types=1);

namespace MedCare\Repositories\Pdo;

use MedCare\Repositories\Contracts\MedicoRepositoryInterface;
use PDO;

class MedicoRepository implements MedicoRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarTodos(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM medicos ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function todos(): array
    {
        return $this->listarTodos();
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->porId($id);
    }

    public function cidades(): array
    {
        $stmt = $this->pdo->query("SELECT DISTINCT cidade FROM medicos ORDER BY cidade ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function buscar(array $filtros = []): array
    {
        $sql = "SELECT * FROM medicos WHERE 1=1";
        $params = [];

        if (!empty($filtros['nome'])) {
            $sql .= " AND nome LIKE ?";
            $params[] = "%" . $filtros['nome'] . "%";
        }

        if (!empty($filtros['cidade'])) {
            $sql .= " AND cidade = ?";
            $params[] = $filtros['cidade'];
        }

        if (!empty($filtros['especialidade'])) {
            $sql .= " AND especialidade_id = ?";
            $params[] = $filtros['especialidade'];
        }

        $sql .= " ORDER BY nome ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM medicos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function destaques(int $limite = 4): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM medicos ORDER BY nome ASC LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function avaliacoes(int $medicoId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM avaliacoes WHERE medico_id = ? ORDER BY id DESC");
        $stmt->execute([$medicoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
