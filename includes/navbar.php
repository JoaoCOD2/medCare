<?php
/** Navbar principal. Variável opcional: $pagina (chave do item ativo). */
$pagina = $pagina ?? '';
$itens = [
    'inicio'        => ['Início',          url('index.php')],
    'medicos'       => ['Encontrar Médico', url('pages/medicos.php')],
    'especialidades'=> ['Especialidades',   url('pages/especialidades.php')],
    'como'          => ['Como Funciona',    url('index.php') . '#como-funciona'],
    'sobre'         => ['Sobre Nós',        url('index.php') . '#sobre'],
    'contato'       => ['Contato',          url('index.php') . '#contato'],
];
?>
<header class="mc-header">
  <nav class="navbar navbar-expand-lg" aria-label="Navegação principal">
    <div class="container">
      <?php include __DIR__ . '/partials/logo.php'; ?>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
              aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="collapse navbar-collapse" id="menuPrincipal">
        <ul class="navbar-nav mx-lg-auto mb-0 mt-3 mt-lg-0">
          <?php foreach ($itens as $chave => [$rotulo, $href]): ?>
            <li class="nav-item">
              <a class="nav-link <?= $pagina === $chave ? 'active' : '' ?>" href="<?= e($href) ?>"
                 <?= $pagina === $chave ? 'aria-current="page"' : '' ?>><?= e($rotulo) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="acoes">
          <a class="btn btn-ghost" href="<?= e(url('pages/login.php')) ?>">Entrar</a>
          <a class="btn btn-mc-outline" href="<?= e(url('pages/cadastro.php')) ?>">Criar conta</a>
          <a class="btn btn-mc" href="<?= e(url('pages/agendamento.php')) ?>"><i class="fa-regular fa-calendar-check me-1"></i>Agendar consulta</a>
        </div>
      </div>
    </div>
  </nav>
</header>
