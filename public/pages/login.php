<?php declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\Csrf;

$titulo = 'Entrar na Conta';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<section class="secao pt-4">
  <div class="container">
    <div class="auth-wrap" style="max-width: 480px; margin: 0 auto;">
      <div class="auth-card">
        <h1>Entrar no MedCare</h1>
        <p class="text-muted-mc">Acesse sua conta para gerenciar suas consultas.</p>
        
        <form id="form-login-definitivo" method="POST" action="<?= e(url('api/login.php')) ?>" autocomplete="off" novalidate>
          <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
          
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="login_email">E-mail</label>
              <input class="form-control" type="email" id="login_email" name="login_email" autocomplete="off" required>
            </div>
            
            <div class="col-12">
              <label class="form-label" for="login_senha">Senha</label>
              <input class="form-control" type="password" id="login_senha" name="login_senha" autocomplete="current-password" required>
            </div>
          </div>
          
          <button class="btn btn-mc w-100 btn-lg mt-4" type="button" id="btn-entrar-login-definitivo">Entrar</button>
          
          <p class="text-center small text-muted-mc mt-3 mb-0 mx-auto">Ainda não tem conta? <a href="<?= e(url('pages/cadastro.php')) ?>">Criar conta</a></p>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-login-definitivo');
    var botao = document.getElementById('btn-entrar-login-definitivo');

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
                // Alerta nativo de sucesso
                alert(resultado.mensagem);
                
                // Usa a função oficial do sistema para gerar o link correto do index da página inicial
                window.location.href = '<?= e(url('')) ?>';
            } else {
                // Alerta nativo para credenciais incorretas
                alert(resultado.erro);
            }
        })
        .catch(function () {
            alert('Erro ao processar o login. Tente novamente.');
        });
    });
});
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
