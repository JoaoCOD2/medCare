/* MedCare — cancelamento de consulta com modal de confirmação. */
(function () {
  'use strict';
  var modalEl = document.getElementById('modalCancelar');
  if (!modalEl) return;
  var modal = new bootstrap.Modal(modalEl);
  var csrf = document.querySelector('meta[name="csrf-token"]').content;
  var alvo = null;

  document.querySelectorAll('.js-cancelar').forEach(function (b) {
    b.addEventListener('click', function () { alvo = b.dataset.numero; modal.show(); });
  });

  document.getElementById('btn-cancelar-final').addEventListener('click', function () {
    var b = this;
    b.disabled = true;
    var original = b.textContent;
    b.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Cancelando…';
    fetch(modalEl.dataset.api, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
      body: JSON.stringify({ numero: alvo })
    }).then(function (r) { return r.json(); }).then(function (j) {
      modal.hide();
      if (j.ok) { mcToast('Consulta cancelada.'); setTimeout(function () { location.reload(); }, 900); }
      else mcToast(j.erro || 'Não foi possível cancelar.', 'erro');
    }).catch(function () { modal.hide(); mcToast('Sem conexão. Tente novamente.', 'erro'); })
      .finally(function () { b.disabled = false; b.textContent = original; });
  });
})();
