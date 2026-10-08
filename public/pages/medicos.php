<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Container;
use MedCare\Services\BuscaService;

$resultado      = (new BuscaService())->buscar($_GET);
$medicos        = $resultado['medicos'];
$f              = $resultado['filtros'];
$especialidades = Container::especialidades()->todas();
$cidades        = Container::medicos()->cidades();
$temFiltro      = array_filter($f) !== [];

$titulo = 'Encontrar médico';
$pagina = 'medicos';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="pagina-topo">
  <div class="container">
    <nav aria-label="Você está em"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= e(url('index.php')) ?>">Início</a></li>
      <li class="breadcrumb-item active" aria-current="page">Encontrar médico</li>
    </ol></nav>
    <h1>Encontrar médico</h1>
    <p class="text-muted-mc mb-4">Filtre por especialidade, local, modalidade e data.</p>

    <form class="filtros" method="get" action="<?= e(url('pages/medicos.php')) ?>" role="search">
      <div class="row g-3">
        <div class="col-md-6 col-lg-3">
          <label class="form-label" for="f-q">Nome do médico</label>
          <input class="form-control" id="f-q" name="q" value="<?= e($f['q']) ?>" placeholder="Ex.: Carlos Almeida">
        </div>
        <div class="col-md-6 col-lg-3">
          <label class="form-label" for="f-esp">Especialidade</label>
          <select class="form-select" id="f-esp" name="especialidade">
            <option value="">Todas</option>
            <?php foreach ($especialidades as $esp): ?>
              <option value="<?= (int) $esp['id'] ?>" <?= $f['especialidade_id'] === $esp['id'] ? 'selected' : '' ?>><?= e($esp['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6 col-lg-2">
          <label class="form-label" for="f-cidade">Cidade</label>
          <input class="form-control" id="f-cidade" name="cidade" list="cidades" value="<?= e($f['cidade']) ?>" placeholder="Todas">
          <datalist id="cidades"><?php foreach ($cidades as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
        </div>
        <div class="col-md-6 col-lg-2">
          <label class="form-label" for="f-bairro">Bairro</label>
          <input class="form-control" id="f-bairro" name="bairro" value="<?= e($f['bairro']) ?>" placeholder="Todos">
        </div>
        <div class="col-md-6 col-lg-2">
          <label class="form-label" for="f-atend">Atendimento</label>
          <select class="form-select" id="f-atend" name="atendimento">
            <option value="">Presencial ou online</option>
            <option value="presencial" <?= $f['atendimento'] === 'presencial' ? 'selected' : '' ?>>Presencial</option>
            <option value="online" <?= $f['atendimento'] === 'online' ? 'selected' : '' ?>>Online</option>
          </select>
        </div>
        <div class="col-md-6 col-lg-3">
          <label class="form-label" for="f-data">Data disponível</label>
          <input class="form-control" type="date" id="f-data" name="data" min="<?= e(date('Y-m-d')) ?>" value="<?= e($f['data']) ?>">
        </div>
        <div class="col-lg-9 d-flex align-items-end gap-2 flex-wrap">
          <button class="btn btn-mc px-4" type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar</button>
          <?php if ($temFiltro): ?><a class="btn btn-ghost" href="<?= e(url('pages/medicos.php')) ?>">Limpar filtros</a><?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</section>

<section class="secao pt-4" aria-live="polite">
  <div class="container">
    <p class="text-muted-mc mb-4"><b><?= count($medicos) ?></b> <?= count($medicos) === 1 ? 'médico encontrado' : 'médicos encontrados' ?></p>

    <?php if ($medicos): ?>
      <div class="row g-4">
        <?php foreach ($medicos as $m): ?>
          <div class="col-md-6 col-xl-4"><?php include dirname(__DIR__, 2) . '/includes/partials/medico_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="vazio">
        <i class="fa-regular fa-face-frown-open" aria-hidden="true"></i>
        <h2 class="h4">Nenhum médico encontrado</h2>
        <p>Tente remover algum filtro, buscar outra data ou ampliar a cidade.</p>
        <a class="btn btn-mc" href="<?= e(url('pages/medicos.php')) ?>">Limpar filtros</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
