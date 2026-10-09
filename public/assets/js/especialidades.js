/* Lista completa de especialidades. */
MC.pronto.then(function () {
  'use strict';
  var lista = MC.$('#lista-esp');
  MC.api.get('api/filtros.php').then(function (r) {
    r.especialidades.forEach(function (e) {
      var col = MC.el('div', { class: 'col-sm-6 col-lg-4' });
      var a = MC.el('a', { class: 'servico h-100', href: 'medicos.html?especialidade=' + e.id }, [
        MC.el('span', { class: 'icone' }, [MC.el('i', { class: 'fa-solid ' + e.icone, 'aria-hidden': 'true' })]),
        MC.el('h3', { text: e.nome }),
        MC.el('p', { text: e.descricao }),
        MC.el('span', { class: 'mais', text: e.total_medicos + (e.total_medicos === 1 ? ' profissional' : ' profissionais') })
      ]);
      col.appendChild(a); lista.appendChild(col);
    });
  }).catch(function () { MC.toast('Não foi possível carregar as especialidades.', 'erro'); });
});
