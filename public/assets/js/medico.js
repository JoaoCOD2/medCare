/* Perfil do médico (medico.html?id=1). */
MC.pronto.then(function () {
  'use strict';
  var id = parseInt(MC.params().get('id'), 10) || 0;
  var diaAtivo = null, medico = null;

  MC.api.get('api/medico.php', { id: id }).then(function (r) {
    medico = r.medico;
    desenharPerfil(r.medico, r.avaliacoes);
    desenharDias(r.dias);
    MC.$('#perfil').hidden = false;
    document.title = medico.nome + ' — ' + medico.especialidade + ' | MedCare';
  }).catch(function (e) {
    if (e.status === 404) { MC.$('#perfil-erro').hidden = false; document.title = 'Médico não encontrado | MedCare'; }
    else MC.toast(e.message, 'erro');
  });

  function lista(ul, itens, icone) {
    MC.limpar(ul);
    itens.forEach(function (t) {
      ul.appendChild(MC.el('li', {}, [MC.el('i', { class: 'fa-solid ' + icone }), MC.el('span', { text: t })]));
    });
  }

  function desenharPerfil(m, avaliacoes) {
    var av = MC.$('#p-avatar'); av.textContent = m.iniciais; av.style.background = m.cor;
    MC.$('#bc-nome').textContent = m.nome;
    MC.$('#p-nome').textContent = m.nome;
    MC.$('#p-sub').textContent = m.especialidade + ' · CRM-' + m.crm_uf + ' ' + m.crm;
    MC.$('#p-nota').textContent = MC.fmt.nota(m.nota);
    MC.$('#p-total').textContent = '(' + m.total_avaliacoes + ' avaliações)';
    var presencial = m.modalidade === 'presencial';
    MC.$('#p-modalidade').classList.toggle('teal', !presencial);
    MC.$('#p-modalidade-icone').classList.add(presencial ? 'fa-building' : 'fa-video');
    MC.$('#p-modalidade-texto').textContent = MC.fmt.modalidade(m.modalidade);
    MC.$('#p-cidade').textContent = m.cidade + ' – ' + m.estado;
    MC.$('#p-bio').textContent = m.biografia;
    lista(MC.$('#p-formacao'), m.formacao, 'fa-graduation-cap');
    lista(MC.$('#p-experiencia'), m.experiencia, 'fa-briefcase');
    MC.$('#p-clinica').textContent = m.clinica;
    MC.$('#p-endereco').textContent = m.endereco + ' – ' + m.bairro + ', ' + m.cidade + '/' + m.estado;
    MC.$('#p-valor').textContent = MC.fmt.moeda(m.valor_consulta);
    var conv = MC.limpar(MC.$('#p-convenios'));
    m.convenios.forEach(function (c) { conv.appendChild(MC.el('span', { class: 'badge-mc', text: c })); });

    var av2 = MC.limpar(MC.$('#p-avaliacoes'));
    avaliacoes.forEach(function (a) {
      var estrelas = MC.el('span', { class: 'nota', 'aria-label': 'Nota ' + a.nota + ' de 5' });
      for (var i = 0; i < a.nota; i++) estrelas.appendChild(MC.el('i', { class: 'fa-solid fa-star' }));
      av2.appendChild(MC.el('div', { class: 'avaliacao' }, [
        MC.el('div', { class: 'd-flex justify-content-between flex-wrap gap-1' }, [MC.el('b', { text: a.nome }), estrelas]),
        MC.el('p', { class: 'mb-1 mt-1', text: a.comentario }),
        MC.el('small', { class: 'text-muted-mc', text: MC.fmt.dataBr(a.data) })
      ]));
    });
    MC.$('#p-agendar').href = 'pages/agendamento.php?medico_id=' + m.id;
  }

  function desenharDias(dias) {
    var c = MC.limpar(MC.$('#h-dias'));
    MC.$('#h-vazio').hidden = dias.length > 0;
    MC.$('#h-conteudo').hidden = dias.length === 0;
    dias.forEach(function (d) {
      var b = MC.el('button', { type: 'button', title: d.livres + ' horários livres', 'data-data': d.data }, [
        document.createTextNode(MC.fmt.diaSemana(d.data)), MC.el('b', { text: MC.fmt.dia(d.data) })
      ]);
      b.addEventListener('click', function () { carregarHorarios(d.data); });
      c.appendChild(b);
    });
    if (dias[0]) carregarHorarios(dias[0].data);
  }

  function carregarHorarios(data) {
    diaAtivo = data;
    MC.$$('#h-dias button').forEach(function (b) {
      var ativo = b.getAttribute('data-data') === data;
      b.classList.toggle('ativo', ativo);
      if (ativo) b.setAttribute('aria-current', 'date'); else b.removeAttribute('aria-current');
    });
    var longa = MC.fmt.dataLonga(data);
    MC.$('#h-data').textContent = longa.charAt(0).toUpperCase() + longa.slice(1);
    MC.api.get('api/horarios.php', { medico_id: medico.id, data: data }).then(function (r) {
      if (data !== diaAtivo) return;
      var g = MC.limpar(MC.$('#h-horas'));
      r.slots.forEach(function (s) {
        if (s.status === 'disponivel') {
          g.appendChild(MC.el('a', { class: 'slot', text: s.hora, href: 'pages/agendamento.php?medico_id=' + medico.id + '&horario=' + encodeURIComponent(s.id) }));
        } else {
          g.appendChild(MC.el('span', { class: 'slot ocupado', text: s.hora, 'aria-label': s.hora + ' ocupado' }));
        }
      });
    }).catch(function (e) { MC.toast(e.message, 'erro'); });
  }
});
