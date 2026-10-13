<?php
declare(strict_types=1);

namespace MedCare\Repositories\Pdo;

use MedCare\Repositories\Contracts\ConsultaRepositoryInterface;
use PDO;

class ConsultaRepository implements ConsultaRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function criar(array $dados): bool
    {
        $sql = "INSERT INTO consultas (numero_consulta, medico_id, horario_id, paciente_id, modalidade, status) 
                VALUES (?, ?, ?, ?, ?, 'agendado')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $dados['numero_consulta'],
            $dados['medico_id'],
            $dados['horario_id'],
            $dados['paciente_id'],
            $dados['modalidade']
        ]);
    }

    public function listarPorPaciente(int $pacienteId): array
    {
        $sql = "SELECT c.*, m.nome AS medico_nome, m.cidade, m.bairro, h.data_disponivel, h.hora_disponivel 
                FROM consultas c
                JOIN medicos m ON c.medico_id = m.id
                JOIN horarios h ON c.horario_id = h.id
                WHERE c.paciente_id = ?
                ORDER BY h.data_disponivel DESC, h.hora_disponivel DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$pacienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelar(int $id): bool
    {
        $sql = "UPDATE consultas SET status = 'cancelado' WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
