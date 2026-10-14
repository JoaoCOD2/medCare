<?php
declare(strict_types=1);

/**
 * Helpers para normalizar os dados de consulta e médico.
 * Salvar em: src/Helpers/consulta_helpers.php
 */

use MedCare\Core\Container;

if (!function_exists('ch_pegar')) {
    function ch_pegar($dados, array $chaves, $padrao = null)
    {
        if (!is_array($dados)) {
            return $padrao;
        }
        foreach ($chaves as $chave) {
            if (isset($dados[$chave]) && is_scalar($dados[$chave]) && $dados[$chave] !== '') {
                return $dados[$chave];
            }
        }
        return $padrao;
    }
}

if (!function_exists('ch_data_hora')) {
    function ch_data_hora(array $linha): array
    {
        $ignorar = '/(criad|created|atualiz|updated|cancel|nasc|deleted|excluid)/i';

        foreach ($linha as $v) {
            if (is_scalar($v) && preg_match('/^\d+-(\d{4})(\d{2})(\d{2})-(\d{2})(\d{2})$/', (string) $v, $x)) {
                return ["{$x[1]}-{$x[2]}-{$x[3]}", "{$x[4]}:{$x[5]}"];
            }
        }

        $data = '';
        $hora = '';
        foreach ($linha as $k => $v) {
            if (!is_scalar($v) || preg_match($ignorar, (string) $k)) {
                continue;
            }
            $v = trim((string) $v);
            if ($data === '' && preg_match('/^(\d{4}-\d{2}-\d{2})(?:[ T](\d{2}:\d{2}))?/', $v, $x)) {
                $data = $x[1];
                if ($hora === '' && !empty($x[2])) {
                    $hora = $x[2];
                }
                continue;
            }
            if ($hora === '' && preg_match('/^(\d{2}:\d{2})(?::\d{2})?$/', $v, $x)) {
                $hora = $x[1];
            }
        }
        return [$data, $hora];
    }
}

if (!function_exists('consulta_normalizar')) {
    function consulta_normalizar($consulta): array
    {
        if (!is_array($consulta)) {
            return ['numero' => '', 'data' => '', 'hora' => '', 'tipo' => 'presencial', 'medico_id' => 0];
        }

        $data = (string) ch_pegar($consulta, ['data', 'data_consulta', 'dia'], '');
        $hora = (string) ch_pegar($consulta, ['hora', 'hora_consulta', 'horario', 'hora_inicio'], '');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $data) || !preg_match('/^\d{2}:\d{2}/', $hora)) {
            [$dAuto, $hAuto] = ch_data_hora($consulta);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $data)) {
                $data = $dAuto;
            }
            if (!preg_match('/^\d{2}:\d{2}/', $hora)) {
                $hora = $hAuto;
            }
        }

        return array_merge($consulta, [
            'numero'    => (string) ch_pegar($consulta, ['numero', 'numero_consulta', 'codigo', 'protocolo'], ''),
            'data'      => substr($data, 0, 10),
            'hora'      => substr($hora, 0, 5),
            'tipo'      => (string) ch_pegar($consulta, ['tipo', 'modalidade', 'tipo_atendimento'], 'presencial'),
            'medico_id' => (int) ch_pegar($consulta, ['medico_id', 'id_medico'], 0),
        ]);
    }
}

if (!function_exists('medico_normalizar')) {
    function medico_normalizar($m): array
    {
        if (!is_array($m)) {
            return ['nome' => '—', 'especialidade' => '—'];
        }

        $esp = ch_pegar($m, ['especialidade', 'especialidade_nome', 'nome_especialidade', 'esp_nome']);
        if ($esp === null || is_numeric($esp)) {
            $esp = null;
            foreach ($m as $k => $v) {
                if (is_scalar($v) && $v !== '' && !is_numeric($v) && stripos((string) $k, 'especialidade') !== false) {
                    $esp = $v;
                    break;
                }
            }
        }

        if ($esp === null) {
            $id = (int) ch_pegar($m, ['especialidade_id', 'id_especialidade'], 0);
            if ($id > 0) {
                try {
                    if (method_exists(Container::class, 'especialidades')) {
                        $repo = Container::especialidades();
                        foreach (['porId', 'buscar', 'find'] as $metodo) {
                            if (method_exists($repo, $metodo)) {
                                $e = $repo->$metodo($id);
                                $esp = is_array($e) ? ch_pegar($e, ['nome', 'descricao', 'titulo']) : null;
                                break;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $esp = null;
                }
            }
        }

        return array_merge($m, [
            'nome'          => (string) ch_pegar($m, ['nome', 'medico_nome'], '—'),
            'especialidade' => $esp !== null ? (string) $esp : '—',
        ]);
    }
}