<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Csrf;

$titulo = 'Criar conta';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="secao pt-4">
  <div class="container">
    <div class="auth-wrap" style="max-width: 640px;">
      <div class="auth-card">
        <h1>Criar conta</h1>
        <p class="text-muted-mc">Leva menos de um minuto. Seus dados são protegidos.</p>
        <form id="form-cadastro" method="post" action="<?= e(url('api/cadastro.php')) ?>" data-fase="Gravação do paciente no MySQL (CPF/e-mail únicos + password_hash) chega na Fase 4." novalidate>
          <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="nome">Nome completo</label>
              <input class="form-control" id="nome" name="nome" autocomplete="name" required minlength="5">
              <div class="invalid-feedback">Informe nome e sobrenome.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="cpf">CPF</label>
              <input class="form-control" id="cpf" name="cpf" inputmode="numeric" placeholder="000.000.000-00" data-mascara="cpf" required>
              <div class="invalid-feedback">CPF inválido.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nascimento">Data de nascimento</label>
              <input class="form-control" type="date" id="nascimento" name="nascimento" max="<?= e(date('Y-m-d')) ?>" required>
              <div class="invalid-feedback">Informe uma data válida.</div>
            </div>
            <div class="col-md-7">
              <label class="form-label" for="email">E-mail</label>
              <input class="form-control" type="email" id="email" name="email" autocomplete="email" required>
              <div class="invalid-feedback">Informe um e-mail válido.</div>
            </div>
            <div class="col-md-5">
              <label class="form-label" for="telefone">Telefone</label>
              <input class="form-control" id="telefone" name="telefone" inputmode="tel" placeholder="(51) 90000-0000" data-mascara="telefone" required>
              <div class="invalid-feedback">Informe DDD e número.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="senha">Senha</label>
              <input class="form-control" type="password" id="senha" name="senha" autocomplete="new-password" minlength="8" required>
              <div class="invalid-feedback">Mínimo de 8 caracteres.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="senha2">Confirmar senha</label>
              <input class="form-control" type="password" id="senha2" name="senha2" autocomplete="new-password" required>
              <div class="invalid-feedback">As senhas não coincidem.</div>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="termos" name="termos" required>
                <label class="form-check-label" for="termos">Li e aceito os <a href="#">termos de uso</a> e a <a href="#">política de privacidade</a>.</label>
                <div class="invalid-feedback">É necessário aceitar para criar a conta.</div>
              </div>
            </div>
          </div>
          <button class="btn btn-mc w-100 btn-lg mt-4" type="submit">Criar conta</button>
          <p class="text-center small text-muted-mc mt-3 mb-0 mx-auto">Já tem conta? <a href="<?= e(url('pages/login.php')) ?>">Entrar</a></p>
        </form>
      </div>
    </div>
  </div>
</section>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
