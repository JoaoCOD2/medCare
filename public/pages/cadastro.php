<?php declare(strict_types=1);

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
        
        <form id="form-cadastro-definitivo" method="POST" action="<?= e(url('api/cadastro.php')) ?>" autocomplete="off" novalidate>
          <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="mc_n">Nome completo</label>
              <input class="form-control" id="mc_n" name="mc_n" autocomplete="off" required minlength="5">
              <div class="invalid-feedback">Informe nome e sobrenome.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="mc_c">CPF</label>
              <input class="form-control" id="mc_c" name="mc_c" inputmode="numeric" placeholder="000.000.000-00" data-mascara="cpf" autocomplete="off" required>
              <div class="invalid-feedback">CPF inválido.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="mc_d">Data de nascimento</label>
              <input class="form-control" type="date" id="mc_d" name="mc_d" max="<?= e(date('Y-m-d')) ?>" autocomplete="off" required>
              <div class="invalid-feedback">Informe uma data válida.</div>
            </div>
            <div class="col-md-7">
              <label class="form-label" for="mc_e">E-mail</label>
              <input class="form-control" type="email" id="mc_e" name="mc_e" autocomplete="off" required>
              <div class="invalid-feedback">Informe um e-mail válido.</div>
            </div>
            <div class="col-md-5">
              <label class="form-label" for="mc_t">Telefone</label>
              <input class="form-control" id="mc_t" name="mc_t" inputmode="tel" placeholder="(51) 90000-0000" data-mascara="telefone" autocomplete="off" required>
              <div class="invalid-feedback">Informe DDD e número.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="mc_s">Senha</label>
              <input class="form-control" type="password" id="mc_s" name="mc_s" autocomplete="new-password" required minlength="8">
              <div class="invalid-feedback">Mínimo de 8 caracteres.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="mc_s2">Confirmar senha</label>
              <input class="form-control" type="password" id="mc_s2" name="mc_s2" autocomplete="new-password" required>
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
          
          <!-- Botão do tipo button inteligente gerenciado pelo Fetch logo abaixo -->
          <button class="btn btn-mc w-100 btn-lg mt-4" type="button" id="btn-enviar-cadastro-definitivo">Criar conta</button>
          <p class="text-center small text-muted-mc mt-3 mb-0 mx-auto">Já tem conta? <a href="<?= e(url('pages/login.php')) ?>">Entrar</a></p>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-cadastro-definitivo');
    var botao = document.getElementById('btn-enviar-cadastro-definitivo');

    botao.addEventListener('click', function () {
        var dadosFormulario = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: dadosFormulario
        })
        .then(function (resposta) {
            return resposta.json();
        })
        .then(function (resultado) {
            if (resultado.sucesso) {
                // Alerta amigável e bonito na tela
                alert(resultado.mensagem);
                
                // Limpa todos os campos da tela automaticamente
                form.reset();
                
                // Redireciona de forma dinâmica para a sua página de login
                window.location.href = '<?= e(url("pages/login.php")) ?>';
            } else {
                // Mostra o erro amigável caso falte dados ou seja duplicado
                alert(resultado.erro);
            }
        })
        .catch(function () {
            alert('Erro ao processar o cadastro. Tente novamente.');
        });
    });
});
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
