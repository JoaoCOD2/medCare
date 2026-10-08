<?php
declare(strict_types=1);

namespace MedCare\Repositories\Contracts;

interface ConsultaRepositoryInterface
{
    /**
     * Cria a consulta e reserva o horário de forma ATÔMICA.
     * No MySQL: transação + SELECT ... FOR UPDATE + UNIQUE em horario_ativo_id.
     * @throws \DomainException se o horário já foi reservado.
     */
    public function criar(int $pacienteId, int $medicoId, string|int $horarioId, string $tipo): array;

    public function porNumero(string $numero, int $pacienteId): ?array;

    /** @return array[] */
    public function doPaciente(int $pacienteId): array;

    /** Cancela e devolve o horário à agenda. @throws \DomainException */
    public function cancelar(string $numero, int $pacienteId): void;
}
