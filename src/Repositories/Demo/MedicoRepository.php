<?php
declare(strict_types=1);

namespace MedCare\Repositories\Demo;

use MedCare\Demo\DemoData;
use MedCare\Repositories\Contracts\MedicoRepositoryInterface;

final class MedicoRepository implements MedicoRepositoryInterface
{
    public function buscar(array $filtros = []): array
    {
        $q       = normalizar((string) ($filtros['q'] ?? ''));
        $espId   = (int) ($filtros['especialidade_id'] ?? 0);
        $cidade  = normalizar((string) ($filtros['cidade'] ?? ''));
        $bairro  = normalizar((string) ($filtros['bairro'] ?? ''));
        $atend   = (string) ($filtros['atendimento'] ?? '');

        $resultado = array_filter(DemoData::medicos(), static function (array $m) use ($q, $espId, $cidade, $bairro, $atend) {
            if ($q !== '' && !str_contains(normalizar($m['nome'] . ' ' . $m['especialidade']), $q)) {
                return false;
            }
            if ($espId && $m['especialidade_id'] !== $espId) {
                return false;
            }
            if ($cidade !== '' && !str_contains(normalizar($m['cidade']), $cidade)) {
                return false;
            }
            if ($bairro !== '' && !str_contains(normalizar($m['bairro']), $bairro)) {
                return false;
            }
            if (in_array($atend, ['presencial', 'online'], true)
                && $m['modalidade'] !== $atend && $m['modalidade'] !== 'ambos') {
                return false;
            }
            return true;
        });

        usort($resultado, static fn($a, $b) => $b['nota'] <=> $a['nota']);
        return array_values($resultado);
    }

    public function porId(int $id): ?array
    {
        foreach (DemoData::medicos() as $m) {
            if ($m['id'] === $id) {
                return $m;
            }
        }
        return null;
    }

    public function destaques(int $limite = 4): array
    {
        return array_slice($this->buscar(), 0, $limite);
    }

    public function cidades(): array
    {
        $cidades = array_unique(array_column(DemoData::medicos(), 'cidade'));
        sort($cidades);
        return $cidades;
    }

    public function avaliacoes(int $medicoId): array
    {
        $modelos = DemoData::avaliacoes();
        $saida = [];
        for ($i = 0; $i < 3; $i++) {
            [$nome, $nota, $texto] = $modelos[($medicoId + $i * 2) % count($modelos)];
            $saida[] = [
                'nome' => $nome, 'nota' => $nota, 'comentario' => $texto,
                'data' => date('Y-m-d', strtotime('-' . (12 + $i * 19 + $medicoId) . ' days')),
            ];
        }
        return $saida;
    }
}
