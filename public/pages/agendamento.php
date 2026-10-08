<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\{Auth, Container};

$medicos = Container::medicos()->buscar();
$especialidades = Container::especialidades()->todas();
$paciente = Auth::pacienteAtual();

// Dados mínimos que o assistente (JS) precisa — sem biografia etc.
$dadosJs = [
    'especialidades' => array_map(static fn($e) => ['id' => $e['id'], 'nome' => $e['nome']], $especialidades),
    'medicos' => array_map(static fn($m) => [
        'id' => $m['id'], 'nome' => $m['nome'], 'iniciais' => iniciais($m['nome']), 'cor' => cor_avatar($m['nome']),
        'especialidade_id' => $m['especialidade_id'], 'especialidade' => $m['especialidade'],
        'modalidade' => $m['modalidade'], 'clinica' => $m['clinica'], 'endereco' => $m['endereco'],
        'cidade' => $m['cidade'], 'valor' => moeda($m['valor_consulta']),
    ], $medicos),
    'inicial' => [
        'medico_id' => (int) ($_GET['medico_id'] ?? 0),
        'horario'   => entrada('horario'),
    ],
    'paciente' => ['nome' => $paciente['nome'], 'email' => $paciente['email'], 'demo' => !empty($paciente['demo'])],
    'urls' => [
        'horarios'   => url('api/horarios.php'),
        'agendar'    => url('api/agendamento.php'),
    ],
];

$titulo = 'Agendar consulta';
$pagina = 'medicos';
$extraJs = ['js/agendamento.js'];
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="pagina-topo">
  <div class="container">
    <h1>Agendar consulta</h1>
    <p class="text-muted-mc mb-0">Em cinco etapas rápidas. Você pode voltar e alterar qualquer escolha.</p>
  </div>
</section>

<section class="secao pt-4">
  <div class="container" style="max-width: 920px;">
    <ol class="etapas list-unstyled" id="etapas" aria-label="Etapas do agendamento">
      <?php foreach (['Médico', 'Especialidade', 'Data', 'Horário', 'Confirmar'] as $i => $nome): ?>
        <li class="etapa" data-etapa="<?= $i + 1 ?>"><span><?= $i + 1 ?></span><?= e($nome) ?></li>
      <?php endforeach; ?>
    </ol>

    <div id="alerta" class="alert alert-danger d-none" role="alert"></div>

    <!-- 1. Médico -->
    <div class="painel-etapa" data-painel="1">
      <h2 class="h4 mb-3">Quem vai atender você?</h2>
      <label class="form-label" for="filtro-esp">Filtrar por especialidade</label>
      <select class="form-select mb-3" id="filtro-esp" style="max-width: 360px;">
        <option value="">Todas as especialidades</option>
      </select>
      <div class="d-grid gap-2" id="lista-medicos" role="radiogroup" aria-label="Médicos"></div>
    </div>

    <!-- 2. Especialidade e tipo -->
    <div class="painel-etapa" data-painel="2">
      <h2 class="h4 mb-3">Especialidade e tipo de atendimento</h2>
      <div class="resumo mb-3">
        <dl>
          <dt>Médico</dt><dd id="e2-medico"></dd>
          <dt>Especialidade</dt><dd id="e2-esp"></dd>
          <dt>Local</dt><dd id="e2-local"></dd>
        </dl>
      </div>
      <fieldset>
        <legend class="form-label">Como você prefere ser atendido?</legend>
        <div class="d-flex gap-2 flex-wrap" id="tipos"></div>
      </fieldset>
    </div>

    <!-- 3. Data -->
    <div class="painel-etapa" data-painel="3">
      <h2 class="h4 mb-3">Escolha a data</h2>
      <div class="faixa-dias" id="lista-dias" style="grid-template-columns: repeat(auto-fill, minmax(64px, 1fr));"></div>
    </div>

    <!-- 4. Horário -->
    <div class="painel-etapa" data-painel="4">
      <h2 class="h4 mb-1">Escolha o horário</h2>
      <p class="text-muted-mc" id="e4-data"></p>
      <div class="grade-horas" id="lista-horas" style="grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));"></div>
    </div>

    <!-- 5. Confirmar -->
    <div class="painel-etapa" data-painel="5">
      <h2 class="h4 mb-3">Confirme os dados</h2>
      <div class="resumo mb-3">
        <dl>
          <dt>Médico</dt><dd id="r-medico"></dd>
          <dt>Especialidade</dt><dd id="r-esp"></dd>
          <dt>Data</dt><dd id="r-data"></dd>
          <dt>Horário</dt><dd id="r-hora"></dd>
          <dt>Atendimento</dt><dd id="r-tipo"></dd>
          <dt>Local</dt><dd id="r-local"></dd>
          <dt>Valor</dt><dd id="r-valor"></dd>
          <dt>Paciente</dt><dd id="r-paciente"></dd>
        </dl>
      </div>
      <?php if (!empty($paciente['demo'])): ?>
        <p class="small text-muted-mc"><i class="fa-solid fa-circle-info me-1"></i>Modo demonstração: o login real chega na Fase 4. Por ora o agendamento usa um paciente de exemplo e não é salvo permanentemente.</p>
      <?php endif; ?>
    </div>

    <div class="d-flex justify-content-between mt-4 gap-2">
      <button type="button" class="btn btn-ghost" id="btn-voltar"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</button>
      <button type="button" class="btn btn-mc" id="btn-avancar">Continuar</button>
    </div>
  </div>
</section>

<!-- Modal de confirmação -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalConfirmarTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius: var(--r-lg);">
    <div class="modal-header border-0"><h2 class="modal-title h4" id="modalConfirmarTitulo">Confirmar agendamento?</h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
    <div class="modal-body pt-0" id="modalResumo"></div>
    <div class="modal-footer border-0">
      <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Revisar</button>
      <button type="button" class="btn btn-mc" id="btn-confirmar-final">Confirmar agendamento</button>
    </div>
  </div></div>
</div>

<script type="application/json" id="dados-agendamento"><?= json_encode($dadosJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
