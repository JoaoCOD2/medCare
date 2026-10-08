<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\{Auth, Container};

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
        <?php foreach ($consultas as $c): $m = $medicos->porId($c['medico_id']); $cancelavel = in_array($c['status'], ['confirmada', 'aguardando'], true); ?>
          <article class="bloco mb-0 d-flex flex-wrap gap-3 align-items-center justify-content-between" data-numero="<?= e($c['numero']) ?>">
            <div class="d-flex gap-3 align-items-center">
              <div class="avatar" style="background:<?= e(cor_avatar($m['nome'])) ?>" aria-hidden="true"><?= e(iniciais($m['nome'])) ?></div>
              <div>
                <h2 class="h5 mb-0"><?= e($m['nome']) ?> <small class="text-muted-mc fw-normal">· <?= e($m['especialidade']) ?></small></h2>
                <div class="text-muted-mc small">
                  <?= e(data_br($c['data'])) ?> às <?= e(hora_curta($c['hora'])) ?> · <?= e($c['tipo'] === 'online' ? 'Online' : $m['clinica']) ?> · <?= e($c['numero']) ?>
                </div>
              </div>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
              <span class="badge-status st-<?= e($c['status']) ?>"><?= e(rotulo_status($c['status'])) ?></span>
              <a class="btn btn-mc-outline btn-sm" href="<?= e(url('pages/medico.php', ['id' => $m['id']])) ?>">Ver detalhes</a>
              <?php if ($cancelavel): ?>
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
