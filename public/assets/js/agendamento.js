/* MedCare — assistente de agendamento em 5 etapas.
   Fala com /api/horarios.php (GET) e /api/agendamento.php (POST).
   Nenhum dado é guardado no navegador: o estado vive só em memória. */
(function () {
  'use strict';

  var D = JSON.parse(document.getElementById('dados-agendamento').textContent);
  var csrf = document.querySelector('meta[name="csrf-token"]').content;
  var $ = function (s) { return document.querySelector(s); };

  var estado = { etapa: 1, medico: null, tipo: null, data: null, horario: null };
  var NOMES_TIPO = { presencial: 'Presencial', online: 'Online (vídeo)' };

  var btnVoltar = $('#btn-voltar'), btnAvancar = $('#btn-avancar'), alerta = $('#alerta');
  var modal = new bootstrap.Modal($('#modalConfirmar'));

  // ---------- utilitários ----------
  function el(tag, attrs, filhos) {
    var n = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (k) {
      if (k === 'class') n.className = attrs[k];
      else if (k === 'text') n.textContent = attrs[k];
      else n.setAttribute(k, attrs[k]);
    });
    (filhos || []).forEach(function (f) { n.appendChild(f); });
    return n;
  }
  function dataLonga(iso) {
    return new Date(iso + 'T00:00:00').toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
  }
  function dataBr(iso) { return iso.split('-').reverse().join('/'); }
  function erro(msg) { alerta.textContent = msg; alerta.classList.remove('d-none'); alerta.scrollIntoView({ block: 'nearest' }); }
  function limparErro() { alerta.classList.add('d-none'); }
  function getJson(url) {
    return fetch(url, { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); });
  }

  // ---------- Etapa 1: médico ----------
  var filtro = $('#filtro-esp');
  D.especialidades.forEach(function (e) { filtro.appendChild(el('option', { value: e.id, text: e.nome })); });
  filtro.addEventListener('change', renderMedicos);

  function renderMedicos() {
    var lista = $('#lista-medicos');
    lista.textContent = '';
    var esp = parseInt(filtro.value, 10) || 0;
    D.medicos.filter(function (m) { return !esp || m.especialidade_id === esp; }).forEach(function (m) {
      var sel = estado.medico && estado.medico.id === m.id;
      var b = el('button', { type: 'button', class: 'opcao-medico' + (sel ? ' selecionado' : ''), role: 'radio', 'aria-checked': sel ? 'true' : 'false' }, [
        el('span', { class: 'avatar', style: 'background:' + m.cor, text: m.iniciais, 'aria-hidden': 'true' }),
        el('span', {}, [
          el('strong', { text: m.nome }), el('br'),
          el('small', { class: 'text-muted-mc', text: m.especialidade + ' · ' + m.cidade + ' · ' + m.valor })
        ])
      ]);
      b.addEventListener('click', function () { escolherMedico(m); renderMedicos(); });
      lista.appendChild(b);
    });
  }

  function escolherMedico(m) {
    if (!estado.medico || estado.medico.id !== m.id) {
      estado.data = null; estado.horario = null;
    }
    estado.medico = m;
    var tipos = m.modalidade === 'ambos' ? ['presencial', 'online'] : [m.modalidade];
    if (tipos.indexOf(estado.tipo) === -1) estado.tipo = tipos[0];
    atualizarBotoes();
  }

  // ---------- Etapa 2: especialidade e tipo ----------
  function renderEtapa2() {
    var m = estado.medico;
    $('#e2-medico').textContent = m.nome;
    $('#e2-esp').textContent = m.especialidade;
    $('#e2-local').textContent = m.clinica + ' — ' + m.endereco + ', ' + m.cidade;
    var cont = $('#tipos');
    cont.textContent = '';
    var tipos = m.modalidade === 'ambos' ? ['presencial', 'online'] : [m.modalidade];
    tipos.forEach(function (t) {
      var b = el('button', { type: 'button', class: 'slot px-4' + (estado.tipo === t ? ' selecionado' : ''), text: NOMES_TIPO[t] });
      b.addEventListener('click', function () { estado.tipo = t; renderEtapa2(); atualizarBotoes(); });
      cont.appendChild(b);
    });
  }

  // ---------- Etapa 3: data ----------
  function renderEtapa3() {
    var cont = $('#lista-dias');
    cont.textContent = 'Carregando datas…';
    getJson(D.urls.horarios + '?medico_id=' + estado.medico.id).then(function (r) {
      cont.textContent = '';
      if (!r.ok || !r.dias.length) { cont.textContent = 'Este médico não tem datas abertas no momento.'; return; }
      r.dias.forEach(function (d) {
        var b = el('button', { type: 'button', class: estado.data === d.data ? 'ativo' : '', title: d.livres + ' horários livres' }, [
          document.createTextNode(d.rotulo), el('b', { text: d.dia })
        ]);
        b.addEventListener('click', function () { estado.data = d.data; estado.horario = null; renderEtapa3(); atualizarBotoes(); });
        cont.appendChild(b);
      });
    }).catch(function () { cont.textContent = ''; erro('Não foi possível carregar as datas. Tente novamente.'); });
  }

  // ---------- Etapa 4: horário ----------
  function renderEtapa4() {
    $('#e4-data').textContent = dataLonga(estado.data);
    var cont = $('#lista-horas');
    cont.textContent = 'Carregando horários…';
    return getJson(D.urls.horarios + '?medico_id=' + estado.medico.id + '&data=' + encodeURIComponent(estado.data)).then(function (r) {
      cont.textContent = '';
      if (!r.ok || !r.slots.length) { cont.textContent = 'Sem horários nesta data.'; return; }
      r.slots.forEach(function (s) {
        var livre = s.status === 'disponivel';
        var sel = estado.horario && estado.horario.id === s.id;
        var b = el('button', { type: 'button', class: 'slot' + (sel ? ' selecionado' : ''), text: s.hora });
        if (!livre) { b.disabled = true; b.setAttribute('aria-label', s.hora + ' ocupado'); }
        else b.addEventListener('click', function () { estado.horario = { id: s.id, hora: s.hora }; renderEtapa4(); atualizarBotoes(); });
        cont.appendChild(b);
      });
    });
  }

  // ---------- Etapa 5: resumo ----------
  function renderEtapa5() {
    var m = estado.medico;
    $('#r-medico').textContent = m.nome;
    $('#r-esp').textContent = m.especialidade;
    $('#r-data').textContent = dataBr(estado.data) + ' (' + dataLonga(estado.data).split(',')[0] + ')';
    $('#r-hora').textContent = estado.horario.hora;
    $('#r-tipo').textContent = NOMES_TIPO[estado.tipo];
    $('#r-local').textContent = estado.tipo === 'online' ? 'Link de acesso enviado antes da consulta' : m.clinica + ' — ' + m.endereco;
    $('#r-valor').textContent = m.valor + ' (particular)';
    $('#r-paciente').textContent = D.paciente.nome + ' · ' + D.paciente.email;
  }

  // ---------- Navegação ----------
  function podeAvancar() {
    switch (estado.etapa) {
      case 1: return !!estado.medico;
      case 2: return !!estado.tipo;
      case 3: return !!estado.data;
      case 4: return !!estado.horario;
      default: return true;
    }
  }
  function atualizarBotoes() {
    btnAvancar.disabled = !podeAvancar();
    btnAvancar.textContent = estado.etapa === 5 ? 'Confirmar agendamento' : 'Continuar';
    btnVoltar.style.visibility = estado.etapa === 1 ? 'hidden' : 'visible';
  }
  function ir(n) {
    limparErro();
    estado.etapa = n;
    document.querySelectorAll('.painel-etapa').forEach(function (p) { p.classList.toggle('ativo', +p.dataset.painel === n); });
    document.querySelectorAll('.etapa').forEach(function (li) {
      var i = +li.dataset.etapa;
      li.classList.toggle('atual', i === n);
      li.classList.toggle('ok', i < n);
      if (i < n) { li.setAttribute('tabindex', '0'); li.setAttribute('role', 'button'); li.style.cursor = 'pointer'; }
      else { li.removeAttribute('tabindex'); li.removeAttribute('role'); li.style.cursor = ''; }
      if (i === n) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
    });
    if (n === 1) renderMedicos();
    if (n === 2) renderEtapa2();
    if (n === 3) renderEtapa3();
    if (n === 4) renderEtapa4();
    if (n === 5) renderEtapa5();
    atualizarBotoes();
    var topo = $('#etapas'); if (topo) topo.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }
  document.querySelectorAll('.etapa').forEach(function (li) {
    function voltarPara() { var i = +li.dataset.etapa; if (i < estado.etapa) ir(i); }
    li.addEventListener('click', voltarPara);
    li.addEventListener('keydown', function (ev) { if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); voltarPara(); } });
  });
  btnVoltar.addEventListener('click', function () { if (estado.etapa > 1) ir(estado.etapa - 1); });
  btnAvancar.addEventListener('click', function () {
    if (!podeAvancar()) return;
    if (estado.etapa < 5) return ir(estado.etapa + 1);
    abrirModal();
  });

  // ---------- Confirmação ----------
  function abrirModal() {
    var m = estado.medico, r = $('#modalResumo');
    r.textContent = '';
    [['Médico', m.nome], ['Data', dataBr(estado.data)], ['Horário', estado.horario.hora], ['Atendimento', NOMES_TIPO[estado.tipo]]]
      .forEach(function (p) {
        var linha = el('div', { class: 'd-flex justify-content-between py-1' }, [
          el('span', { class: 'text-muted-mc', text: p[0] }), el('strong', { text: p[1] })
        ]);
        r.appendChild(linha);
      });
    modal.show();
  }

  $('#btn-confirmar-final').addEventListener('click', function () {
    var b = this;
    b.disabled = true;
    b.classList.add('is-loading');
    var original = b.textContent;
    b.innerHTML = '<span class="spinner-border" aria-hidden="true"></span>Agendando…';

    fetch(D.urls.agendar, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
      body: JSON.stringify({ medico_id: estado.medico.id, horario_id: estado.horario.id, tipo: estado.tipo })
    }).then(function (r) { return r.json().then(function (j) { return { status: r.status, corpo: j }; }); })
      .then(function (res) {
        if (res.corpo.ok) { window.location.href = res.corpo.redirect; return; }
        modal.hide();
        erro(res.corpo.erro || 'Não foi possível concluir o agendamento.');
        if (res.status === 409) { estado.horario = null; ir(4); erro(res.corpo.erro); }
      })
      .catch(function () { modal.hide(); erro('Sem conexão. Verifique sua internet e tente de novo.'); })
      .finally(function () { b.disabled = false; b.classList.remove('is-loading'); b.textContent = original; });
  });

  // ---------- Início (aceita ?medico_id= e ?horario= vindos de outras páginas) ----------
  function iniciar() {
    var m = D.medicos.filter(function (x) { return x.id === D.inicial.medico_id; })[0];
    if (!m) return ir(1);
    escolherMedico(m);

    var p = /^(\d+)-(\d{4})(\d{2})(\d{2})-(\d{2})(\d{2})$/.exec(D.inicial.horario || '');
    if (!p || +p[1] !== m.id) return ir(2);

    var data = p[2] + '-' + p[3] + '-' + p[4];
    getJson(D.urls.horarios + '?medico_id=' + m.id + '&data=' + data).then(function (r) {
      var slot = r.ok && r.slots.filter(function (s) { return s.id === D.inicial.horario && s.status === 'disponivel'; })[0];
      if (!slot) { estado.data = data; ir(4); erro('Esse horário não está mais disponível. Escolha outro.'); return; }
      estado.data = data; estado.horario = { id: slot.id, hora: slot.hora };
      ir(5);
    }).catch(function () { ir(2); });
  }
  iniciar();
})();
