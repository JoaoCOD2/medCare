<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Container;

$especialidades = Container::especialidades()->todas();
$titulo = 'Especialidades';
$pagina = 'especialidades';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="pagina-topo">
  <div class="container">
    <nav aria-label="Você está em"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= e(url('index.php')) ?>">Início</a></li>
      <li class="breadcrumb-item active" aria-current="page">Especialidades</li>
    </ol></nav>
    <h1>Encontre atendimento por especialidade</h1>
    <p class="text-muted-mc mb-0">Escolha uma área para ver os médicos disponíveis.</p>
  </div>
</section>
<section class="secao pt-5">
  <div class="container">
    <div class="row g-4">
      <?php foreach ($especialidades as $esp): ?>
        <div class="col-sm-6 col-lg-4">
          <a class="servico h-100" href="<?= e(url('pages/medicos.php', ['especialidade' => $esp['id']])) ?>">
            <span class="icone"><i class="fa-solid <?= e($esp['icone']) ?>" aria-hidden="true"></i></span>
            <h3><?= e($esp['nome']) ?></h3>
            <p><?= e($esp['descricao']) ?></p>
            <span class="mais"><?= (int) $esp['total_medicos'] ?> <?= $esp['total_medicos'] === 1 ? 'profissional' : 'profissionais' ?></span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
