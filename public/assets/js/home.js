/* Página inicial: especialidades, médicos em destaque e cartão "agenda ao vivo". */
MC.pronto.then(function () {
  'use strict';
  var hoje = new Date().toISOString().slice(0, 10);
  MC.$('#h-data').min = hoje;

  // ---- Especialidades e cidades
  MC.api.get('api/filtros.php').then(function (r) {
    var grade = MC.$('#grade-esp');
    r.especialidades.forEach(function (e) {
      grade.appendChild(MC.preencher(MC.modelo('tpl-esp'), {
        url: 'medicos.html?especialidade=' + e.id,
        icone_classe: 'fa-solid ' + e.icone,
        nome: e.nome,
        contagem: e.total_medicos + (e.total_medicos === 1 ? ' profissional' : ' profissionais')
      }));
    });
    var lista = MC.$('#lista-cidades');
    r.cidades.forEach(function (c) { lista.appendChild(MC.el('option', { value: c })); });
  }).catch(function () { MC.toast('Não foi possível carregar as especialidades.', 'erro'); });

  // ---- Médicos em destaque + cartão do hero
  MC.api.get('api/medicos.php', { limite: 4 }).then(function (r) {
    var cont = MC.$('#destaques');
    r.medicos.forEach(function (m) {
      var col = MC.el('div', { class: 'col-md-6 col-xl-3' });
      col.appendChild(MC.cartaoMedico(m));
      cont.appendChild(col);
    });
    if (r.medicos[0]) return montarAgendaViva(r.medicos[0]);
  }).catch(function () { MC.toast('Não foi possível carregar os médicos.', 'erro'); });

  function montarAgendaViva(m) {
    return MC.api.get('api/horarios.php', { medico_id: m.id }).then(function (r) {
      var dias = r.dias.slice(0, 5);
      if (!dias.length) return null;
      return MC.api.get('api/horarios.php', { medico_id: m.id, data: dias[0].data }).then(function (h) {
        var av = MC.$('#av-avatar');
        av.textContent = m.iniciais; av.style.background = m.cor;
        MC.$('#av-nome').textContent = m.nome;
        MC.$('#av-esp').textContent = m.especialidade;
        MC.$('#av-nota').textContent = MC.fmt.nota(m.nota);

        var cd = MC.limpar(MC.$('#av-dias'));
        dias.forEach(function (d, i) {
          cd.appendChild(MC.el('div', { class: 'dia-chip' + (i === 0 ? ' ativo' : '') }, [
            document.createTextNode(MC.fmt.diaSemana(d.data)), MC.el('b', { text: MC.fmt.dia(d.data) })
          ]));
        });

        var ch = MC.limpar(MC.$('#av-horas')), primeiro = true;
        h.slots.slice(0, 6).forEach(function (s) {
          if (s.status !== 'disponivel') { ch.appendChild(MC.el('span', { class: 'slot ocupado', text: s.hora, 'aria-label': 'Horário ocupado' })); return; }
          ch.appendChild(MC.el('a', {
            class: 'slot' + (primeiro ? ' destaque' : ''), text: s.hora,
            href: 'agendamento.html?medico_id=' + m.id + '&horario=' + encodeURIComponent(s.id)
          }));
          primeiro = false;
        });
        MC.$('#coluna-agenda').hidden = false;
      });
    });
  }
});
