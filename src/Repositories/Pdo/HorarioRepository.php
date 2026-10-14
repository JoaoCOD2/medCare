<?php

declare(strict_types=1);

namespace MedCare\Repositories\Pdo;

use MedCare\Repositories\Contracts\HorarioRepositoryInterface;
use PDO;

final class HorarioRepository implements HorarioRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function doDia(int $medicoId, string $data): array
    {
        $sql = "SELECT id, medico_id, data_disponivel,
                       hora_disponivel, ocupado
                FROM horarios
                WHERE medico_id = ?
                  AND data_disponivel = ?
                ORDER BY hora_disponivel ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$medicoId, $data]);

        $slots = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hora = substr((string) $row['hora_disponivel'], 0, 5);
            $ocupado = (int) $row['ocupado'] === 1;

            $slots[] = [
                'id'          => (string) $row['id'],
                'medico_id'   => (int) $row['medico_id'],
                'data'        => $row['data_disponivel'],
                'hora_inicio' => $hora,
                'hora_fim'    => date(
                    'H:i',
                    strtotime($hora . ' +30 minutes')
                ),
                'status'      => $ocupado ? 'reservado' : 'disponivel',
            ];
        }

        return $slots;
    }

    public function dias(int $medicoId, int $quantidade = 7): array
    {
        $dias = [];
        $data = date('Y-m-d');
        $limite = date('Y-m-d', strtotime('+30 days'));

        while (count($dias) < $quantidade && $data <= $limite) {
            $slots = $this->doDia($medicoId, $data);

            if ($slots !== []) {
                $livres = count(array_filter(
                    $slots,
                    static fn(array $slot): bool =>
                        $slot['status'] === 'disponivel'
                ));

                $dias[] = [
                    'data' => $data,
                    'livres' => $livres,
                ];
            }

            $data = date('Y-m-d', strtotime($data . ' +1 day'));
        }

        return $dias;
    }

    public function proximoDisponivel(int $medicoId): ?array
    {
        foreach ($this->dias($medicoId, 30) as $dia) {
            foreach ($this->doDia($medicoId, $dia['data']) as $slot) {
                if ($slot['status'] === 'disponivel') {
                    return $slot;
                }
            }
        }

        return null;
    }

    public function porId(string|int $id): ?array
    {
        if (!ctype_digit((string) $id)) {
            return null;
        }

        $sql = "SELECT id, medico_id, data_disponivel,
                       hora_disponivel, ocupado
                FROM horarios
                WHERE id = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int) $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $hora = substr((string) $row['hora_disponivel'], 0, 5);

        return [
            'id'          => (string) $row['id'],
            'medico_id'   => (int) $row['medico_id'],
            'data'        => $row['data_disponivel'],
            'hora_inicio' => $hora,
            'hora_fim'    => date('H:i', strtotime($hora . ' +30 minutes')),
            'status'      => (int) $row['ocupado'] === 1
                ? 'reservado'
                : 'disponivel',
        ];
    }
}
