/* Busca de médicos: lê os filtros da URL, consulta a API e desenha os cartões. */
MC.pronto.then(function () {
  'use strict';
  var params = MC.params();
  var campos = { q: '#f-q', especialidade: '#f-esp', cidade: '#f-cidade', bairro: '#f-bairro', atendimento: '#f-atend', data: '#f-data' };
  MC.$('#f-data').min = new Date().toISOString().slice(0, 10);

  function preencherFiltros() {
    Object.keys(campos).forEach(function (k) { MC.$(campos[k]).value = params.get(k) || ''; });
    var algum = Object.keys(campos).some(function (k) { return params.get(k); });
    MC.$('#limpar').hidden = !algum;
  }

  // Listas dos filtros (precisam existir antes de selecionar o valor da URL)
  var filtros = MC.api.get('api/filtros.php').then(function (r) {
    var sel = MC.$('#f-esp');
    r.especialidades.forEach(function (e) { sel.appendChild(MC.el('option', { value: e.id, text: e.nome })); });
    var dl = MC.$('#cidades');
    r.cidades.forEach(function (c) { dl.appendChild(MC.el('option', { value: c })); });
  });

  filtros.then(preencherFiltros).then(function () {
    var q = {};
    Object.keys(campos).forEach(function (k) { q[k] = params.get(k) || ''; });
    return MC.api.get('api/medicos.php', q);
  }).then(function (r) {
    MC.$('#contador').innerHTML = '<b>' + r.total + '</b> ' + (r.total === 1 ? 'médico encontrado' : 'médicos encontrados');
    var cont = MC.limpar(MC.$('#resultados'));
    r.medicos.forEach(function (m) {
      var col = MC.el('div', { class: 'col-md-6 col-xl-4' });
      col.appendChild(MC.cartaoMedico(m));
      cont.appendChild(col);
    });
    MC.$('#vazio').hidden = r.total > 0;
  }).catch(function (e) {
    MC.$('#contador').textContent = '';
    MC.toast(e.message || 'Não foi possível buscar médicos.', 'erro');
  });
});
