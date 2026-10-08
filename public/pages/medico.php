<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Container;

$id = (int) ($_GET['id'] ?? 0);
$m  = $id > 0 ? Container::medicos()->porId($id) : null;
if (!$m) {
    http_response_code(404);
    $titulo = 'Médico não encontrado';
    include dirname(__DIR__, 2) . '/includes/header.php';
    echo '<section class="secao"><div class="container"><div class="vazio"><i class="fa-regular fa-circle-question"></i>'
       . '<h1 class="h3">Médico não encontrado</h1><p>O perfil que você procura não existe ou foi desativado.</p>'
       . '<a class="btn btn-mc" href="' . e(url('pages/medicos.php')) . '">Buscar médicos</a></div></div></section>';
    include dirname(__DIR__, 2) . '/includes/footer.php';
    exit;
}

$horarios = Container::horarios();
$dias     = $horarios->dias($m['id'], 5);
$dataSel  = entrada('data');
if (!in_array($dataSel, array_column($dias, 'data'), true)) {
    $dataSel = $dias[0]['data'] ?? '';
}
$slots      = $dataSel ? $horarios->doDia($m['id'], $dataSel) : [];
$avaliacoes = Container::medicos()->avaliacoes($m['id']);

$titulo = $m['nome'] . ' — ' . $m['especialidade'];
$descricao = mb_substr($m['biografia'], 0, 150);
$pagina = 'medicos';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="pagina-topo">
  <div class="container">
    <nav aria-label="Você está em"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= e(url('index.php')) ?>">Início</a></li>
      <li class="breadcrumb-item"><a href="<?= e(url('pages/medicos.php')) ?>">Médicos</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= e($m['nome']) ?></li>
    </ol></nav>
    <div class="perfil-cab">
      <div class="avatar avatar-lg" style="background:<?= e(cor_avatar($m['nome'])) ?>" aria-hidden="true"><?= e(iniciais($m['nome'])) ?></div>
      <div>
        <h1 class="mb-1"><?= e($m['nome']) ?></h1>
        <div class="fw-semibold" style="color:var(--mc-green-600)"><?= e($m['especialidade']) ?> · CRM-<?= e($m['crm_uf']) ?> <?= e($m['crm']) ?></div>
        <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
          <span class="nota"><i class="fa-solid fa-star"></i><?= number_format($m['nota'], 1, ',', '') ?>
            <span class="text-muted-mc fw-normal">(<?= (int) $m['total_avaliacoes'] ?> avaliações)</span></span>
          <span class="badge-mc <?= $m['modalidade'] === 'presencial' ? '' : 'teal' ?>"><i class="fa-solid <?= $m['modalidade'] === 'presencial' ? 'fa-building' : 'fa-video' ?>"></i><?= e(rotulo_modalidade($m['modalidade'])) ?></span>
          <span class="badge-mc"><i class="fa-solid fa-location-dot"></i><?= e($m['cidade']) ?> – <?= e($m['estado']) ?></span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="secao pt-4">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-7 col-xl-8">
        <div class="bloco"><h2>Sobre</h2><p class="mb-0"><?= e($m['biografia']) ?></p></div>
        <div class="row g-0 gx-lg-3">
          <div class="col-md-6"><div class="bloco h-100"><h2>Formação</h2>
            <ul class="lista"><?php foreach ($m['formacao'] as $x): ?><li><i class="fa-solid fa-graduation-cap"></i><span><?= e($x) ?></span></li><?php endforeach; ?></ul></div></div>
          <div class="col-md-6"><div class="bloco h-100"><h2>Experiência</h2>
            <ul class="lista"><?php foreach ($m['experiencia'] as $x): ?><li><i class="fa-solid fa-briefcase"></i><span><?= e($x) ?></span></li><?php endforeach; ?></ul></div></div>
        </div>
        <div class="bloco"><h2>Local de atendimento</h2>
          <p class="mb-1"><b><?= e($m['clinica']) ?></b></p>
          <p class="mb-2 text-muted-mc"><?= e($m['endereco']) ?> – <?= e($m['bairro']) ?>, <?= e($m['cidade']) ?>/<?= e($m['estado']) ?></p>
          <p class="mb-0"><i class="fa-solid fa-tag me-2" style="color:var(--mc-green)"></i>Consulta particular: <b><?= e(moeda($m['valor_consulta'])) ?></b></p>
        </div>
        <div class="bloco"><h2>Convênios aceitos</h2>
          <div class="d-flex flex-wrap gap-2"><?php foreach ($m['convenios'] as $c): ?><span class="badge-mc"><?= e($c) ?></span><?php endforeach; ?></div></div>
        <div class="bloco"><h2>Avaliações de pacientes</h2>
          <?php foreach ($avaliacoes as $a): ?>
            <div class="avaliacao">
              <div class="d-flex justify-content-between flex-wrap gap-1">
                <b><?= e($a['nome']) ?></b>
                <span class="nota" aria-label="Nota <?= (int) $a['nota'] ?> de 5"><?= str_repeat('<i class="fa-solid fa-star"></i>', (int) $a['nota']) ?></span>
              </div>
              <p class="mb-1 mt-1"><?= e($a['comentario']) ?></p>
              <small class="text-muted-mc"><?= e(data_br($a['data'])) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <aside class="col-lg-5 col-xl-4" aria-label="Agendamento">
        <div class="painel-horarios">
          <h2>Escolha um horário</h2>
          <?php if ($dias): ?>
            <div class="faixa-dias" role="tablist" aria-label="Dias disponíveis">
              <?php foreach ($dias as $d): ?>
                <a href="<?= e(url('pages/medico.php', ['id' => $m['id'], 'data' => $d['data']])) ?>#horarios"
                   class="<?= $d['data'] === $dataSel ? 'ativo' : '' ?>" <?= $d['data'] === $dataSel ? 'aria-current="date"' : '' ?>
                   title="<?= (int) $d['livres'] ?> horários livres">
                  <?= e(dia_semana_curto($d['data'])) ?><b><?= e(date('d', strtotime($d['data']))) ?></b>
                </a>
              <?php endforeach; ?>
            </div>
            <p class="small text-muted-mc mb-2" id="horarios"><?= e(ucfirst(dia_semana_longo($dataSel))) ?>, <?= e(data_br($dataSel)) ?></p>
            <div class="grade-horas">
              <?php foreach ($slots as $s): ?>
                <?php if ($s['status'] === 'disponivel'): ?>
                  <a class="slot" href="<?= e(url('pages/agendamento.php', ['medico_id' => $m['id'], 'horario' => $s['id']])) ?>"><?= e(hora_curta($s['hora_inicio'])) ?></a>
                <?php else: ?>
                  <span class="slot ocupado" aria-label="<?= e(hora_curta($s['hora_inicio'])) ?> ocupado"><?= e(hora_curta($s['hora_inicio'])) ?></span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
            <div class="legenda"><span><i class="fa-regular fa-square-check me-1" style="color:var(--mc-green)"></i>Disponível</span><span><s>00:00</s> Ocupado</span></div>
          <?php else: ?>
            <p class="text-muted-mc">Este médico não tem horários abertos nos próximos dias.</p>
          <?php endif; ?>
          <a class="btn btn-mc w-100 mt-3" href="<?= e(url('pages/agendamento.php', ['medico_id' => $m['id']])) ?>">Agendar consulta</a>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
