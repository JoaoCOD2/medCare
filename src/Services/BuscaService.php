<?php
declare(strict_types=1);

namespace MedCare\Services;

use MedCare\Core\Container;

/** Regras de busca de médicos. Não conhece SQL nem arrays de demonstração. */
final class BuscaService
{
    /**
     * @param array $params normalmente $_GET
     * @return array{medicos: array[], filtros: array}
     */
    public function buscar(array $params): array
    {
        $filtros = [
            'q'                => trim((string) ($params['q'] ?? '')),
            'especialidade_id' => (int) ($params['especialidade'] ?? 0),
            'cidade'           => trim((string) ($params['cidade'] ?? '')),
            'bairro'           => trim((string) ($params['bairro'] ?? '')),
            'atendimento'      => in_array($params['atendimento'] ?? '', ['presencial', 'online'], true)
                                    ? $params['atendimento'] : '',
            'data'             => $this->dataValida((string) ($params['data'] ?? '')),
        ];

        $medicos = Container::medicos()->buscar($filtros);
        $horarios = Container::horarios();
        $saida = [];

        foreach ($medicos as $m) {
            if ($filtros['data'] !== '') {
                $slots = array_filter(
                    $horarios->doDia($m['id'], $filtros['data']),
                    static fn($s) => $s['status'] === 'disponivel'
                );
                if (!$slots) {
                    continue; // sem vaga na data pedida
                }
                $m['proximo_horario'] = reset($slots);
            } else {
                $m['proximo_horario'] = $horarios->proximoDisponivel($m['id']);
            }
            $saida[] = $m;
        }

        return ['medicos' => $saida, 'filtros' => $filtros];
    }

    private function dataValida(string $data): string
    {
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        return ($d && $d->format('Y-m-d') === $data && $data >= date('Y-m-d')) ? $data : '';
    }
}
