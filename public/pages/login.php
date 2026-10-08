<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Csrf;

$titulo = 'Entrar';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="secao pt-4">
  <div class="container">
    <div class="auth-wrap">
      <div class="auth-card">
        <h1>Entrar</h1>
        <p class="text-muted-mc">Acesse sua conta para agendar e acompanhar consultas.</p>
        <form id="form-login" method="post" action="<?= e(url('api/login.php')) ?>" data-fase="Autenticação real (password_verify + sessão) chega na Fase 4." novalidate>
          <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
          <input type="hidden" name="next" value="<?= e(entrada('next')) ?>">
          <div class="mb-3">
            <label class="form-label" for="identificador">CPF ou e-mail</label>
            <input class="form-control" id="identificador" name="identificador" autocomplete="username" required>
            <div class="invalid-feedback">Informe seu CPF ou e-mail.</div>
          </div>
          <div class="mb-2">
            <label class="form-label" for="senha">Senha</label>
            <input class="form-control" type="password" id="senha" name="senha" autocomplete="current-password" required minlength="8">
            <div class="invalid-feedback">A senha deve ter ao menos 8 caracteres.</div>
          </div>
          <div class="mb-4"><a href="#" class="small" data-fase-link="Recuperação de senha chega na Fase 4.">Esqueci minha senha</a></div>
          <button class="btn btn-mc w-100 btn-lg" type="submit">Entrar</button>
          <a class="btn btn-mc-outline w-100 mt-2" href="<?= e(url('pages/cadastro.php')) ?>">Criar conta</a>
        </form>
      </div>
    </div>
  </div>
</section>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
