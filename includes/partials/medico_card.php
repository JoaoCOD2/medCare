<?php
/** Cartão de médico. Espera $m (com opcional 'proximo_horario'). */
$prox = $m['proximo_horario'] ?? null;
$linkAgendar = url('pages/agendamento.php', [
    'medico_id' => $m['id'],
    'horario'   => $prox['id'] ?? null,
]);
?>
<article class="medico-card">
  <div class="cab">
    <div class="avatar" style="background:<?= e(cor_avatar($m['nome'])) ?>" aria-hidden="true"><?= e(iniciais($m['nome'])) ?></div>
    <div>
      <h3><a href="<?= e(url('pages/medico.php', ['id' => $m['id']])) ?>"><?= e($m['nome']) ?></a></h3>
      <div class="esp-nome"><?= e($m['especialidade']) ?></div>
      <div class="crm">CRM-<?= e($m['crm_uf']) ?> <?= e($m['crm']) ?></div>
    </div>
  </div>

  <div class="meta">
    <div><i class="fa-solid fa-location-dot"></i><?= e($m['cidade']) ?> – <?= e($m['estado']) ?> · <?= e($m['bairro']) ?></div>
    <div><i class="fa-solid fa-hospital"></i><?= e($m['endereco']) ?></div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="badge-mc <?= $m['modalidade'] === 'presencial' ? '' : 'teal' ?>">
        <i class="fa-solid <?= $m['modalidade'] === 'presencial' ? 'fa-building' : 'fa-video' ?>"></i><?= e(rotulo_modalidade($m['modalidade'])) ?>
      </span>
      <span class="nota"><i class="fa-solid fa-star"></i><?= number_format($m['nota'], 1, ',', '') ?>
        <span class="text-muted-mc fw-normal">(<?= (int) $m['total_avaliacoes'] ?>)</span></span>
    </div>
  </div>

  <div class="proximo">
    Próximo horário
    <b><?= $prox ? e(rotulo_horario($prox)) : 'Sem horários nos próximos 30 dias' ?></b>
  </div>

  <div class="acoes">
    <a class="btn btn-mc-outline" href="<?= e(url('pages/medico.php', ['id' => $m['id']])) ?>">Ver perfil</a>
    <a class="btn btn-mc <?= $prox ? '' : 'disabled' ?>" href="<?= e($linkAgendar) ?>">Agendar</a>
  </div>
</article>
