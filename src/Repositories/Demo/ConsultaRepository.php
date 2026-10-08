<?php
declare(strict_types=1);

namespace MedCare\Repositories\Demo;

use DomainException;
use MedCare\Core\Container;
use MedCare\Repositories\Contracts\ConsultaRepositoryInterface;

/**
 * Guarda consultas na SESSÃO PHP apenas para demonstrar o fluxo.
 * Nada é persistido: ao fechar o navegador, some. O MySQL substitui isto.
 */
final class ConsultaRepository implements ConsultaRepositoryInterface
{
    public function criar(int $pacienteId, int $medicoId, string|int $horarioId, string $tipo): array
    {
        $horario = Container::horarios()->porId($horarioId);
        if (!$horario || $horario['status'] !== 'disponivel') {
            throw new DomainException('Este horário acabou de ser reservado. Escolha outro horário.');
        }

        $dataConsulta = str_replace('-', '', $horario['data']);
        $sequencia = 1;
        foreach ($_SESSION['demo_consultas'] ?? [] as $c) {
            if ($c['data'] === $horario['data']) {
                $sequencia++;
            }
        }

        $consulta = [
            'numero'      => sprintf('MC-%s-%03d', $dataConsulta, $sequencia),
            'paciente_id' => $pacienteId,
            'medico_id'   => $medicoId,
            'horario_id'  => (string) $horarioId,
            'data'        => $horario['data'],
            'hora'        => $horario['hora_inicio'],
            'tipo'        => $tipo,
            'status'      => 'confirmada',
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $_SESSION['demo_consultas'][$consulta['numero']] = $consulta;
        $_SESSION['demo_reservados'][(string) $horarioId] = true;
        return $consulta;
    }

    public function porNumero(string $numero, int $pacienteId): ?array
    {
        $c = $_SESSION['demo_consultas'][$numero] ?? null;
        return ($c && $c['paciente_id'] === $pacienteId) ? $c : null;
    }

    public function doPaciente(int $pacienteId): array
    {
        $lista = array_filter(
            $_SESSION['demo_consultas'] ?? [],
            static fn($c) => $c['paciente_id'] === $pacienteId
        );
        usort($lista, static fn($a, $b) => [$a['data'], $a['hora']] <=> [$b['data'], $b['hora']]);
        return $lista;
    }

    public function cancelar(string $numero, int $pacienteId): void
    {
        $c = $this->porNumero($numero, $pacienteId);
        if (!$c) {
            throw new DomainException('Consulta não encontrada.');
        }
        if ($c['status'] === 'concluida') {
            throw new DomainException('Consultas concluídas não podem ser canceladas.');
        }
        if ($c['status'] === 'cancelada') {
            throw new DomainException('Esta consulta já foi cancelada.');
        }
        $_SESSION['demo_consultas'][$numero]['status'] = 'cancelada';
        unset($_SESSION['demo_reservados'][$c['horario_id']]);
    }
}
