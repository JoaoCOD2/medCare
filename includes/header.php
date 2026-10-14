<?php
declare(strict_types=1);

use MedCare\Core\Auth;

$titulo  = $titulo ?? 'MedCare';
$pagina  = $pagina ?? '';
$paciente = Auth::pacienteAtual();
$logado   = $paciente !== null;
$primeiroNome = $logado ? explode(' ', trim((string) ($paciente['nome'] ?? '')))[0] : '';

function mc_ativo(string $item, string $atual): string
{
    return $item === $atual ? ' active' : '';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> — MedCare</title>
  <meta name="csrf-token" content="<?= e(\MedCare\Core\Csrf::token()) ?>">
  <meta name="theme-color" content="#15915F">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400..800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body data-pagina="<?= e($pagina) ?>">
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

<header class="mc-header">
  <nav class="navbar navbar-expand-xl" aria-label="Navegação principal">
    <div class="container">
      <a class="navbar-brand" href="<?= e(url('index.html')) ?>">
        <i class="fa-solid fa-square-plus" style="color:#15915F"></i>
        <strong>MedCare</strong>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
              data-bs-target="#menuPrincipal" aria-controls="menuPrincipal"
              aria-expanded="false" aria-label="Abrir menu">
        <i class="fa-solid fa-bars"></i>
      </button>

      <div class="collapse navbar-collapse" id="menuPrincipal">
        <ul class="navbar-nav mx-xl-auto mb-0 mt-3 mt-xl-0">
          <li class="nav-item">
            <a class="nav-link<?= mc_ativo('inicio', $pagina) ?>" href="<?= e(url('index.html')) ?>">Início</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= mc_ativo('medicos', $pagina) ?>" href="<?= e(url('pages/medicos.php')) ?>">Encontrar Médico</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= mc_ativo('especialidades', $pagina) ?>" href="<?= e(url('pages/especialidades.php')) ?>">Especialidades</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= e(url('index.html')) ?>#como-funciona">Como Funciona</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= e(url('index.html')) ?>#sobre">Sobre Nós</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= e(url('index.html')) ?>#contato">Contato</a>
          </li>
        </ul>

        <div class="acoes">
          <?php if ($logado): ?>
            <div class="user-menu dropdown">
              <button class="btn user-menu-trigger" type="button" data-bs-toggle="dropdown"
                      aria-expanded="false" aria-label="Menu do usuário">
                <i class="fa-solid fa-user"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end user-menu-list">
                <li><span class="dropdown-item-text fw-semibold">Olá, <?= e($primeiroNome) ?></span></li>
                <li><a class="dropdown-item" href="<?= e(url('pages/consultas.php')) ?>">Minha conta</a></li>
                <li><a class="dropdown-item" href="<?= e(url('pages/sair.php')) ?>">Sair</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a class="btn btn-mc-outline" href="<?= e(url('pages/login.php')) ?>">Entrar</a>
            <a class="btn btn-mc" href="<?= e(url('pages/cadastro.php')) ?>">Criar conta</a>
          <?php endif; ?>

          <a class="btn btn-mc" href="<?= e(url('pages/agendamento.php')) ?>">
            <i class="fa-regular fa-calendar-check me-1"></i>Agendar consulta
          </a>
        </div>
      </div>
    </div>
  </nav>
</header>