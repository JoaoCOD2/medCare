<?php
declare(strict_types=1);

namespace MedCare\Services;

use DomainException;
use MedCare\Core\Container;

/** Regras do agendamento. A atomicidade da reserva fica no repositório. */
final class AgendamentoService
{
    public function confirmar(int $pacienteId, int $medicoId, string $horarioId, string $tipo): array
    {
        $medico = Container::medicos()->porId($medicoId);
        if (!$medico) {
            throw new DomainException('Médico não encontrado.');
        }

        if (!in_array($tipo, ['presencial', 'online'], true)
            || ($medico['modalidade'] !== 'ambos' && $medico['modalidade'] !== $tipo)) {
            throw new DomainException('Este médico não atende nesta modalidade.');
        }

        $horario = Container::horarios()->porId($horarioId);
        if (!$horario || (int) $horario['medico_id'] !== $medicoId) {
            throw new DomainException('Horário inválido para este médico.');
        }

        return Container::consultas()->criar($pacienteId, $medicoId, $horarioId, $tipo);
    }
}
