MC.pronto.then(function () {
    'use strict';

    var form = document.getElementById('form-cadastro');
    if (!form) return;

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        // Executa as validações dos campos existentes.
        MC.$$('input', form).forEach(function (campo) {
            campo.dispatchEvent(new Event('blur'));
        });

        if (!form.checkValidity() || form.querySelector('.is-invalid')) {
            MC.toast('Confira os campos destacados antes de continuar.', 'erro');
            return;
        }

        var botao = form.querySelector('button[type="submit"]');
        if (botao.disabled) return;

        botao.disabled = true;

        var dados = new FormData();
        dados.append('mc_n', form.elements.nome.value.trim());
        dados.append('mc_c', form.elements.cpf.value.trim());
        dados.append('mc_d', form.elements.nascimento.value);
        dados.append('mc_e', form.elements.email.value.trim());
        dados.append('mc_t', form.elements.telefone.value.trim());
        dados.append('mc_s', form.elements.senha.value);
        dados.append('mc_s2', form.elements.senha2.value);

        if (form.elements.termos.checked) {
            dados.append('termos', '1');
        }

        fetch('api/cadastro.php', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: dados
        })
        .then(function (resposta) {
            return resposta.json().then(function (resultado) {
                if (!resposta.ok || resultado.sucesso !== true) {
                    throw new Error(
                        resultado.erro || 'Não foi possível concluir o cadastro.'
                    );
                }
                return resultado;
            });
        })
        .then(function (resultado) {
            MC.toast(
                resultado.mensagem || 'Cadastro realizado com sucesso!',
                'sucesso'
            );

            setTimeout(function () {
                window.location.href = 'pages/login.php';
            }, 1200);
        })
        .catch(function (erro) {
            MC.toast(erro.message || 'Erro ao realizar cadastro.', 'erro');
            botao.disabled = false;
        });
    });
});
