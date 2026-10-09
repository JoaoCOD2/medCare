/* Login e cadastro: máscaras e validação no navegador.
   A validação DEFINITIVA é feita no PHP (nunca confie só no front). */
MC.pronto.then(function () {
  'use strict';
  var mascaras = {
    cpf: function (v) {
      v = v.replace(/\D/g, '').slice(0, 11);
      return v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    },
    telefone: function (v) {
      v = v.replace(/\D/g, '').slice(0, 11);
      if (v.length > 10) return v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
      return v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3').replace(/-$/, '');
    }
  };
  MC.$$('[data-mascara]').forEach(function (el) {
    el.addEventListener('input', function () { el.value = mascaras[el.getAttribute('data-mascara')](el.value); });
  });

  var nasc = MC.$('#nascimento');
  if (nasc) nasc.max = new Date().toISOString().slice(0, 10);

  function cpfValido(cpf) {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
    for (var t = 9; t < 11; t++) {
      var soma = 0;
      for (var i = 0; i < t; i++) soma += parseInt(cpf[i], 10) * (t + 1 - i);
      if (((soma * 10) % 11) % 10 !== parseInt(cpf[t], 10)) return false;
    }
    return true;
  }

  function validarCampo(el, form) {
    var ok = el.checkValidity();
    var m = el.getAttribute('data-mascara');
    if (ok && m === 'cpf') ok = cpfValido(el.value);
    if (ok && m === 'telefone') ok = el.value.replace(/\D/g, '').length >= 10;
    if (ok && el.id === 'senha2') ok = el.value === MC.$('#senha', form).value;
    el.classList.toggle('is-invalid', !ok);
    return ok;
  }

  ['form-login', 'form-cadastro'].forEach(function (id) {
    var form = document.getElementById(id);
    if (!form) return;
    var campos = MC.$$('input', form);
    campos.forEach(function (c) {
      c.addEventListener('blur', function () { if (c.value) validarCampo(c, form); });
      c.addEventListener('input', function () { if (c.classList.contains('is-invalid')) validarCampo(c, form); });
    });
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var primeiro = null;
      campos.forEach(function (c) { if (!validarCampo(c, form) && !primeiro) primeiro = c; });
      if (primeiro) { primeiro.focus(); return; }
      MC.toast(form.getAttribute('data-fase'), 'info');   // Fase 4: trocar por MC.api.post('api/login.php', ...)
    });
  });

  MC.$$('[data-fase-link]').forEach(function (a) {
    a.addEventListener('click', function (ev) { ev.preventDefault(); MC.toast(a.getAttribute('data-fase-link'), 'info'); });
  });
});
