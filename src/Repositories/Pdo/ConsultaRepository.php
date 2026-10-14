<?php
declare(strict_types=1);
namespace MedCare\Repositories\Pdo;

use DomainException;
use MedCare\Repositories\Contracts\ConsultaRepositoryInterface;
use PDO;
use Throwable;

final class ConsultaRepository implements ConsultaRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function criar(
        int $pacienteId,
        int $medicoId,
        string|int $horarioId,
        string $tipo
    ): array {
        try {
            $this->pdo->beginTransaction();

            // Bloqueia o horário enquanto a reserva é processada.
            $stmt = $this->pdo->prepare(
                "SELECT id, medico_id, data_disponivel,
                        hora_disponivel, ocupado
                 FROM horarios
                 WHERE id = ? AND medico_id = ?
                 FOR UPDATE"
            );

            $stmt->execute([$horarioId, $medicoId]);
            $horario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$horario || (int) $horario['ocupado'] !== 0) {
                throw new DomainException(
                    'Este horário não está disponível. Escolha outro.'
                );
            }

            $numero = sprintf(
                'MC-%s-%s',
                date('Ymd'),
                strtoupper(bin2hex(random_bytes(4)))
            );

            $sql = "INSERT INTO consultas
                    (numero_consulta, medico_id, horario_id,
                     paciente_id, modalidade, status)
                    VALUES (?, ?, ?, ?, ?, 'agendado')";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $numero,
                $medicoId,
                $horarioId,
                $pacienteId,
                $tipo
            ]);

            // Marca o horário como ocupado somente se ainda estiver livre.
            $stmt = $this->pdo->prepare(
                "UPDATE horarios
                 SET ocupado = 1
                 WHERE id = ? AND medico_id = ? AND ocupado = 0"
            );

            $stmt->execute([$horarioId, $medicoId]);

            if ($stmt->rowCount() !== 1) {
                throw new DomainException(
                    'Não foi possível reservar este horário.'
                );
            }

            $this->pdo->commit();

            return [
                'numero'      => $numero,
                'numero_consulta' => $numero,
                'paciente_id' => $pacienteId,
                'medico_id'   => $medicoId,
                'horario_id'  => (string) $horarioId,
                'data'        => $horario['data_disponivel'],
                'hora'        => $horario['hora_disponivel'],
                'tipo'        => $tipo,
                'status'      => 'agendado'
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function porNumero(
        string $numero,
        int $pacienteId
    ): ?array {
        $sql = "SELECT c.*, m.nome AS medico_nome,
                       m.cidade, m.bairro,
                       h.data_disponivel, h.hora_disponivel
                FROM consultas c
                JOIN medicos m ON m.id = c.medico_id
                JOIN horarios h ON h.id = c.horario_id
                WHERE c.numero_consulta = ?
                  AND c.paciente_id = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$numero, $pacienteId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function doPaciente(int $pacienteId): array
    {
        $sql = "SELECT c.*, m.nome AS medico_nome,
                       m.cidade, m.bairro,
                       h.data_disponivel, h.hora_disponivel
                FROM consultas c
                JOIN medicos m ON m.id = c.medico_id
                JOIN horarios h ON h.id = c.horario_id
                WHERE c.paciente_id = ?
                ORDER BY h.data_disponivel DESC,
                         h.hora_disponivel DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$pacienteId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelar(
        string $numero,
        int $pacienteId
    ): void {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "SELECT id, horario_id, status
                 FROM consultas
                 WHERE numero_consulta = ?
                   AND paciente_id = ?
                 FOR UPDATE"
            );

            $stmt->execute([$numero, $pacienteId]);
            $consulta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$consulta) {
                throw new DomainException('Consulta não encontrada.');
            }

            if (in_array(
                $consulta['status'],
                ['cancelado', 'cancelada'],
                true
            )) {
                throw new DomainException(
                    'Esta consulta já foi cancelada.'
                );
            }

            if (in_array(
                $consulta['status'],
                ['concluido', 'concluida'],
                true
            )) {
                throw new DomainException(
                    'Consultas concluídas não podem ser canceladas.'
                );
            }

            $stmt = $this->pdo->prepare(
                "UPDATE consultas
                 SET status = 'cancelado'
                 WHERE id = ?"
            );

            $stmt->execute([(int) $consulta['id']]);

            $stmt = $this->pdo->prepare(
                "UPDATE horarios
                 SET ocupado = 0
                 WHERE id = ?"
            );

            $stmt->execute([(int) $consulta['horario_id']]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}
