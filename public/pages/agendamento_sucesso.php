<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\{Auth, Container};

$paciente = Auth::pacienteAtual();
$consulta = Container::consultas()->porNumero(entrada('n'), (int) $paciente['id']);
if (!$consulta) {
    redirecionar(url('pages/consultas.php'));
}
$m = Container::medicos()->porId($consulta['medico_id']);

$titulo = 'Consulta agendada';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="secao">
  <div class="container">
    <div class="sucesso">
      <div class="check" aria-hidden="true"><i class="fa-solid fa-check"></i></div>
      <h1 class="h2">Consulta agendada com sucesso!</h1>
      <p class="mx-auto text-muted-mc mb-1">Número da consulta</p>
      <div class="numero-consulta"><?= e($consulta['numero']) ?></div>

      <div class="resumo text-start mb-4">
        <dl>
          <dt>Médico</dt><dd><?= e($m['nome']) ?></dd>
          <dt>Especialidade</dt><dd><?= e($m['especialidade']) ?></dd>
          <dt>Data</dt><dd><?= e(data_br($consulta['data'])) ?> (<?= e(dia_semana_longo($consulta['data'])) ?>)</dd>
          <dt>Horário</dt><dd><?= e(hora_curta($consulta['hora'])) ?></dd>
          <dt>Atendimento</dt><dd><?= e(rotulo_modalidade($consulta['tipo'])) ?></dd>
          <dt>Local</dt><dd><?= $consulta['tipo'] === 'online' ? 'Link de acesso enviado antes da consulta' : e($m['clinica'] . ' — ' . $m['endereco']) ?></dd>
        </dl>
      </div>
      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a class="btn btn-mc btn-lg" href="<?= e(url('pages/consultas.php')) ?>">Ver minha consulta</a>
        <a class="btn btn-mc-outline btn-lg" href="<?= e(url('index.php')) ?>">Voltar para início</a>
      </div>
    </div>
  </div>
</section>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
