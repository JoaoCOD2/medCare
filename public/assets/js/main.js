/* MedCare — comportamento global: toasts, máscaras e validação de formulários. */
(function () {
  'use strict';

  // ---------- Toasts ----------
  window.mcToast = function (mensagem, tipo) {
    var area = document.getElementById('toastArea');
    if (!area) return;
    var t = document.createElement('div');
    t.className = 'mc-toast ' + (tipo || '');
    t.setAttribute('role', tipo === 'erro' ? 'alert' : 'status');
    var icone = { erro: 'fa-circle-exclamation', info: 'fa-circle-info' }[tipo] || 'fa-circle-check';
    t.innerHTML = '<i class="fa-solid ' + icone + ' mt-1"></i><span></span>';
    t.querySelector('span').textContent = mensagem;
    area.appendChild(t);
    setTimeout(function () { t.remove(); }, 5000);
  };

  // ---------- Máscaras ----------
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
  document.querySelectorAll('[data-mascara]').forEach(function (el) {
    el.addEventListener('input', function () { el.value = mascaras[el.dataset.mascara](el.value); });
  });

  // ---------- CPF (mesmo algoritmo será repetido no PHP — nunca confie só no front) ----------
  function cpfValido(cpf) {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
    for (var t = 9; t < 11; t++) {
      var soma = 0;
      for (var i = 0; i < t; i++) soma += parseInt(cpf[i], 10) * (t + 1 - i);
      var dv = ((soma * 10) % 11) % 10;
      if (dv !== parseInt(cpf[t], 10)) return false;
    }
    return true;
  }

  // ---------- Validação de formulários de autenticação ----------
  function validarCampo(el, form) {
    var ok = el.checkValidity();
    if (ok && el.dataset.mascara === 'cpf') ok = cpfValido(el.value);
    if (ok && el.dataset.mascara === 'telefone') ok = el.value.replace(/\D/g, '').length >= 10;
    if (ok && el.id === 'senha2') ok = el.value === form.querySelector('#senha').value;
    el.classList.toggle('is-invalid', !ok);
    return ok;
  }

  ['form-login', 'form-cadastro'].forEach(function (id) {
    var form = document.getElementById(id);
    if (!form) return;
    var campos = form.querySelectorAll('input:not([type=hidden])');
    campos.forEach(function (c) {
      c.addEventListener('blur', function () { if (c.value) validarCampo(c, form); });
      c.addEventListener('input', function () { if (c.classList.contains('is-invalid')) validarCampo(c, form); });
    });
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var todosOk = true, primeiro = null;
      campos.forEach(function (c) {
        if (!validarCampo(c, form)) { todosOk = false; primeiro = primeiro || c; }
      });
      if (!todosOk) { primeiro.focus(); return; }
      // Fase 1: backend de autenticação ainda não existe.
      mcToast(form.dataset.fase, 'info');
    });
  });

  document.querySelectorAll('[data-fase-link]').forEach(function (a) {
    a.addEventListener('click', function (ev) { ev.preventDefault(); mcToast(a.dataset.faseLink, 'info'); });
  });
})();
