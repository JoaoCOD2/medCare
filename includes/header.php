<?php
/**
 * Variáveis esperadas (todas opcionais):
 *   $titulo, $descricao, $pagina (item ativo do menu), $extraCss (array de URLs)
 */
use MedCare\Core\Csrf;

$titulo    = isset($titulo) ? $titulo . ' | ' . config('nome') : config('nome') . ' — Encontre o médico ideal e agende online';
$descricao = $descricao ?? 'Encontre médicos por especialidade, veja horários disponíveis e agende sua consulta de forma rápida e segura.';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?></title>
  <meta name="description" content="<?= e($descricao) ?>">
  <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
  <meta name="theme-color" content="#15915F">
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' rx='13' fill='%2315915F'/%3E%3Cpath d='M17 9h6a2 2 0 0 1 2 2v4h4a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-4v4a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-4h-4a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2h4v-4a2 2 0 0 1 2-2Z' fill='%23fff'/%3E%3C/svg%3E">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
  <?php foreach (($extraCss ?? []) as $css): ?>
    <link href="<?= e(asset($css)) ?>" rel="stylesheet">
  <?php endforeach; ?>
</head>
<body>
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
<?php include __DIR__ . '/navbar.php'; ?>
<main id="conteudo">
