<?php
declare(strict_types=1);

namespace MedCare\Repositories\Demo;

use MedCare\Repositories\Contracts\HorarioRepositoryInterface;

/**
 * Gera uma agenda determinística (seg–sex, 08:00–11:30 e 14:00–17:30, a cada 30 min).
 * Alguns slots já nascem reservados; reservas feitas na sessão também contam.
 * O id é "{medico}-{AAAAMMDD}-{HHMM}" — opaco para as views.
 */
final class HorarioRepository implements HorarioRepositoryInterface
{
    private const HORAS = [
        '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
    ];

    public function doDia(int $medicoId, string $data): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || strtotime($data) === false) {
            return [];
        }
        $diaSemana = (int) date('w', strtotime($data));
        if ($diaSemana === 0 || $diaSemana === 6 || $data < date('Y-m-d')) {
            return [];
        }

        $reservadosSessao = $_SESSION['demo_reservados'] ?? [];
        $agora = date('H:i');
        $slots = [];

        foreach (self::HORAS as $hora) {
            if ($data === date('Y-m-d') && $hora <= $agora) {
                continue; // já passou
            }
            $id = $this->montarId($medicoId, $data, $hora);
            $ocupado = (crc32("{$medicoId}|{$data}|{$hora}") % 100) < 38
                || isset($reservadosSessao[$id]);

            $slots[] = [
                'id'          => $id,
                'medico_id'   => $medicoId,
                'data'        => $data,
                'hora_inicio' => $hora,
                'hora_fim'    => date('H:i', strtotime("{$data} {$hora} +30 minutes")),
                'status'      => $ocupado ? 'reservado' : 'disponivel',
            ];
        }
        return $slots;
    }

    public function dias(int $medicoId, int $quantidade = 7): array
    {
        $dias = [];
        for ($i = 0; count($dias) < $quantidade && $i < 30; $i++) {
            $data = date('Y-m-d', strtotime("+{$i} days"));
            $slots = $this->doDia($medicoId, $data);
            if (!$slots) {
                continue;
            }
            $livres = count(array_filter($slots, static fn($s) => $s['status'] === 'disponivel'));
            $dias[] = ['data' => $data, 'livres' => $livres];
        }
        return $dias;
    }

    public function proximoDisponivel(int $medicoId): ?array
    {
        for ($i = 0; $i < 30; $i++) {
            $data = date('Y-m-d', strtotime("+{$i} days"));
            foreach ($this->doDia($medicoId, $data) as $slot) {
                if ($slot['status'] === 'disponivel') {
                    return $slot;
                }
            }
        }
        return null;
    }

    public function porId(string|int $id): ?array
    {
        if (!preg_match('/^(\d+)-(\d{8})-(\d{4})$/', (string) $id, $p)) {
            return null;
        }
        $data = substr($p[2], 0, 4) . '-' . substr($p[2], 4, 2) . '-' . substr($p[2], 6, 2);
        foreach ($this->doDia((int) $p[1], $data) as $slot) {
            if ($slot['id'] === (string) $id) {
                return $slot;
            }
        }
        return null;
    }

    private function montarId(int $medicoId, string $data, string $hora): string
    {
        return $medicoId . '-' . str_replace('-', '', $data) . '-' . str_replace(':', '', $hora);
    }
}
