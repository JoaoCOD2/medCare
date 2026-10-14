<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\{Auth, Container};

/* ------------------------------------------------------------------
   Funções de normalização (independem do nome das colunas do banco).
   Ficam aqui dentro para esta página funcionar sem arquivos extras.
------------------------------------------------------------------ */
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

if (!function_exists('ch_por_fragmento')) {
    /** Primeiro valor de texto cuja CHAVE contém algum dos fragmentos. */
    function ch_por_fragmento(array $dados, array $fragmentos): string
    {
        foreach ($dados as $k => $v) {
            if (!is_scalar($v) || $v === '' || is_numeric($v)) {
                continue;
            }
            foreach ($fragmentos as $f) {
                if (stripos((string) $k, $f) !== false) {
                    return (string) $v;
                }
            }
        }
        return '';
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
            return ['numero' => '', 'data' => '', 'hora' => '', 'tipo' => 'presencial', 'status' => 'aguardando', 'medico_id' => 0];
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

        // Número da consulta: chaves conhecidas ou qualquer valor no formato MC-AAAAMMDD-XXXXXXXX
        $numero = (string) ch_pegar($consulta, ['numero', 'numero_consulta', 'codigo', 'protocolo'], '');
        if ($numero === '') {
            foreach ($consulta as $v) {
                if (is_scalar($v) && preg_match('/^MC-\d{8}-[0-9A-Z]+$/i', (string) $v)) {
                    $numero = (string) $v;
                    break;
                }
            }
        }

        return array_merge($consulta, [
            'numero'    => $numero,
            'data'      => substr($data, 0, 10),
            'hora'      => substr($hora, 0, 5),
            'tipo'      => (string) ch_pegar($consulta, ['tipo', 'modalidade', 'tipo_atendimento'], 'presencial'),
            'status'    => (string) ch_pegar($consulta, ['status', 'situacao'], 'aguardando'),
            'medico_id' => (int) ch_pegar($consulta, ['medico_id', 'id_medico'], 0),
        ]);
    }
}

if (!function_exists('medico_normalizar')) {
    function medico_normalizar($m): array
    {
        if (!is_array($m)) {
            return ['id' => 0, 'nome' => '—', 'especialidade' => '—', 'clinica' => '—'];
        }

        $esp = ch_pegar($m, ['especialidade', 'especialidade_nome', 'nome_especialidade', 'esp_nome']);
        if ($esp === null || is_numeric($esp)) {
            $esp = ch_por_fragmento($m, ['especialidade']);
            if ($esp === '') {
                $esp = null;
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

        $clinica = (string) ch_pegar($m, ['clinica', 'clinica_nome'], '');
        if ($clinica === '') {
            $clinica = ch_por_fragmento($m, ['clinica', 'consultorio', 'unidade', 'hospital']);
        }

        return array_merge($m, [
            'nome'          => (string) ch_pegar($m, ['nome', 'medico_nome'], '—'),
            'especialidade' => $esp !== null ? (string) $esp : '—',
            'clinica'       => $clinica !== '' ? $clinica : '—',
        ]);
    }
}

/* ------------------------------------------------------------------
   Dados da página
------------------------------------------------------------------ */
$paciente  = Auth::pacienteAtual();
$consultas = Container::consultas()->doPaciente((int) $paciente['id']);
$medicos   = Container::medicos();

$titulo = 'Minhas consultas';
$extraJs = ['js/consultas.js'];
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="pagina-topo">
  <div class="container">
    <h1>Minhas consultas</h1>
    <p class="text-muted-mc mb-0">Acompanhe e gerencie seus atendimentos.</p>
  </div>
</section>
<section class="secao pt-4">
  <div class="container" style="max-width: 920px;">
    <?php if (!$consultas): ?>
      <div class="vazio">
        <i class="fa-regular fa-calendar-plus" aria-hidden="true"></i>
        <h2 class="h4">Você ainda não tem consultas</h2>
        <p>Encontre um médico e escolha o melhor horário.</p>
        <a class="btn btn-mc" href="<?= e(url('pages/medicos.php')) ?>">Encontrar médico</a>
      </div>
    <?php else: ?>
      <div class="d-grid gap-3">
        <?php foreach ($consultas as $c):
            $c = consulta_normalizar($c);
            $m = medico_normalizar($medicos->porId($c['medico_id']));
            $medicoId   = (int) ($m['id'] ?? $c['medico_id']);
            $cancelavel = in_array($c['status'], ['confirmada', 'aguardando'], true);
        ?>
          <article class="bloco mb-0 d-flex flex-wrap gap-3 align-items-center justify-content-between" data-numero="<?= e($c['numero']) ?>">
            <div class="d-flex gap-3 align-items-center">
              <div class="avatar" style="background:<?= e(cor_avatar($m['nome'])) ?>" aria-hidden="true"><?= e(iniciais($m['nome'])) ?></div>
              <div>
                <h2 class="h5 mb-0"><?= e($m['nome']) ?> <small class="text-muted-mc fw-normal">· <?= e($m['especialidade']) ?></small></h2>
                <div class="text-muted-mc small">
                  <?= $c['data'] !== '' ? e(data_br($c['data'])) : '—' ?> às <?= $c['hora'] !== '' ? e(hora_curta($c['hora'])) : '—' ?> · <?= e($c['tipo'] === 'online' ? 'Online' : $m['clinica']) ?> · <?= e($c['numero']) ?>
                </div>
              </div>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
              <span class="badge-status st-<?= e($c['status']) ?>"><?= e(rotulo_status($c['status'])) ?></span>
              <?php if ($medicoId > 0): ?>
                <a class="btn btn-mc-outline btn-sm" href="<?= e(url('pages/medico.php', ['id' => $medicoId])) ?>">Ver detalhes</a>
              <?php endif; ?>
              <?php if ($cancelavel && $c['numero'] !== ''): ?>
                <button class="btn btn-sm btn-ghost text-danger js-cancelar" type="button" data-numero="<?= e($c['numero']) ?>">Cancelar consulta</button>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="modal fade" id="modalCancelar" data-api="<?= e(url('api/cancelar_consulta.php')) ?>" tabindex="-1" aria-labelledby="modalCancelarTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius: var(--r-lg);">
    <div class="modal-header border-0"><h2 class="modal-title h4" id="modalCancelarTitulo">Cancelar consulta?</h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
    <div class="modal-body pt-0">O horário voltará a ficar disponível na agenda do médico. Esta ação não pode ser desfeita.</div>
    <div class="modal-footer border-0">
      <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Manter consulta</button>
      <button type="button" class="btn btn-danger rounded-pill" id="btn-cancelar-final">Cancelar consulta</button>
    </div>
  </div></div>
</div>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>