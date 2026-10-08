<?php
declare(strict_types=1);

namespace MedCare\Repositories\Contracts;

interface HorarioRepositoryInterface
{
    /**
     * Slots de um médico em uma data (Y-m-d).
     * Cada slot: id, medico_id, data, hora_inicio, hora_fim,
     * status ('disponivel'|'reservado'|'bloqueado').
     * O "id" é opaco para as views (int no MySQL).
     * @return array[]
     */
    public function doDia(int $medicoId, string $data): array;

    /** @return array[] data, livres  (próximos dias com agenda) */
    public function dias(int $medicoId, int $quantidade = 7): array;

    public function proximoDisponivel(int $medicoId): ?array;

    public function porId(string|int $id): ?array;
}
