/* Login e cadastro: máscaras e validação no navegador.
   A validação definitiva também deve ser feita no PHP. */

MC.pronto.then(function () {
    'use strict';

    var mascaras = {
        cpf: function (v) {
            v = v.replace(/\D/g, '').slice(0, 11);

            return v
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        },

        telefone: function (v) {
            v = v.replace(/\D/g, '').slice(0, 11);

            if (v.length > 10) {
                return v.replace(
                    /(\d{2})(\d{5})(\d{4})/,
                    '($1) $2-$3'
                );
            }

            return v
                .replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3')
                .replace(/-$/, '');
        }
    };

    /* Aplica as máscaras de CPF e telefone usando as novas IDs. */
    MC.$$('[data-mascara]').forEach(function (el) {
        el.addEventListener('input', function () {
            var tipo = el.getAttribute('data-mascara');

            if (mascaras[tipo]) {
                el.value = mascaras[tipo](el.value);
            }
        });
    });

    /* Impede selecionar uma data de nascimento futura. */
    var nasc = MC.$('#mc_d');

    if (nasc) {
        nasc.max = new Date().toISOString().slice(0, 10);
    }

    /* Valida os dígitos verificadores do CPF. */
    function cpfValido(cpf) {
        cpf = cpf.replace(/\D/g, '');

        if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) {
            return false;
        }

        for (var t = 9; t < 11; t++) {
            var soma = 0;

            for (var i = 0; i < t; i++) {
                soma += parseInt(cpf[i], 10) * (t + 1 - i);
            }

            var digito = ((soma * 10) % 11) % 10;

            if (digito !== parseInt(cpf[t], 10)) {
                return false;
            }
        }

        return true;
    }

    /* Valida cada campo do formulário baseado nos novos identificadores. */
    function validarCampo(el, form) {
        var ok = el.checkValidity();
        var mascara = el.getAttribute('data-mascara');

        if (ok && mascara === 'cpf') {
            ok = cpfValido(el.value);
        }

        if (ok && mascara === 'telefone') {
            ok = el.value.replace(/\D/g, '').length >= 10;
        }

        if (ok && el.id === 'mc_s2') {
            var senha = MC.$('#mc_s', form);
            ok = senha !== null && el.value === senha.value;
        }

        el.classList.toggle('is-invalid', !ok);

        return ok;
    }

    /* Configura as escutas do formulário de cadastro. */
    var formCadastro = document.getElementById('form-cadastro');
    if (formCadastro) {
        var campos = MC.$$('input', formCadastro);

        campos.forEach(function (campo) {
            campo.addEventListener('blur', function () {
                if (campo.value || campo.required) {
                    validarCampo(campo, formCadastro);
                }
            });

            campo.addEventListener('input', function () {
                if (campo.classList.contains('is-invalid')) {
                    validarCampo(campo, formCadastro);
                }
            });
        });
    }
});
