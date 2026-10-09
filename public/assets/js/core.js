/* MedCare — núcleo do frontend (carregado em TODAS as páginas).
   Responsabilidades: incluir componentes HTML, falar com a API PHP,
   formatar dados e oferecer pequenos utilitários de DOM.
   Nenhuma regra de negócio mora aqui: ela vive no PHP. */
(function () {
  'use strict';
  var MC = (window.MC = {});

  // ---------------------------------------------------------------- formatação
  var DIAS = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];
  function paraData(iso) { return new Date(iso + 'T00:00:00'); }
  function isoLocal(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  MC.fmt = {
    dataBr: function (iso) { return iso.split('-').reverse().join('/'); },
    dataLonga: function (iso) { return paraData(iso).toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }); },
    diaSemana: function (iso) { return DIAS[paraData(iso).getDay()]; },
    dia: function (iso) { return iso.slice(8, 10); },
    hora: function (h) { return String(h).slice(0, 5); },
    moeda: function (v) { return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); },
    nota: function (n) { return Number(n).toFixed(1).replace('.', ','); },
    horario: function (h) {          // "Hoje às 15:30", "Amanhã às 09:00", "qui, 08/10 às 14:30"
      var hora = MC.fmt.hora(h.hora_inicio), hoje = new Date(), amanha = new Date(Date.now() + 864e5);
      if (h.data === isoLocal(hoje)) return 'Hoje às ' + hora;
      if (h.data === isoLocal(amanha)) return 'Amanhã às ' + hora;
      return MC.fmt.diaSemana(h.data).toLowerCase() + ', ' + h.data.slice(8, 10) + '/' + h.data.slice(5, 7) + ' às ' + hora;
    },
    modalidade: function (m) { return { presencial: 'Presencial', online: 'Online' }[m] || 'Presencial e online'; },
    status: function (s) {
      return { confirmada: 'Confirmada', aguardando: 'Aguardando confirmação', concluida: 'Concluída', cancelada: 'Cancelada' }[s] || s;
    }
  };

  // ---------------------------------------------------------------- API
  function montarUrl(url, params) {
    var q = new URLSearchParams();
    Object.keys(params || {}).forEach(function (k) {
      if (params[k] !== '' && params[k] != null) q.append(k, params[k]);
    });
    var s = q.toString();
    return s ? url + '?' + s : url;
  }
  function tratar(resposta) {
    return resposta.json().catch(function () { return { ok: false, erro: 'Resposta inválida do servidor.' }; })
      .then(function (j) {
        if (!resposta.ok || j.ok === false) {
          var e = new Error(j.erro || 'Erro inesperado.'); e.status = resposta.status; throw e;
        }
        return j;
      });
  }
  var ctx = null;
  MC.api = {
    get: function (url, params) {
      return fetch(montarUrl(url, params), { headers: { Accept: 'application/json' } }).then(tratar);
    },
    post: function (url, corpo) {
      return MC.contexto().then(function (c) {
        return fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-Token': c.csrf },
          body: JSON.stringify(corpo)
        }).then(tratar);
      });
    }
  };
  /** Dados da sessão (token CSRF, paciente). Buscado uma única vez. */
  MC.contexto = function () { return (ctx = ctx || MC.api.get('api/contexto.php')); };

  // ---------------------------------------------------------------- DOM
  MC.el = function (tag, attrs, filhos) {
    var n = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (k) {
      if (k === 'class') n.className = attrs[k];
      else if (k === 'text') n.textContent = attrs[k];
      else n.setAttribute(k, attrs[k]);
    });
    (filhos || []).forEach(function (f) { n.appendChild(f); });
    return n;
  };
  MC.$ = function (sel, raiz) { return (raiz || document).querySelector(sel); };
  MC.$$ = function (sel, raiz) { return Array.prototype.slice.call((raiz || document).querySelectorAll(sel)); };
  MC.limpar = function (no) { while (no.firstChild) no.removeChild(no.firstChild); return no; };

  /** Clona um <template id="..."> e devolve o primeiro elemento. */
  MC.modelo = function (id) { return document.getElementById(id).content.firstElementChild.cloneNode(true); };

  /** Preenche [data-bind="campo"] (texto) e [data-bind-attr="attr:campo,..."] (atributos). */
  MC.preencher = function (raiz, dados) {
    MC.$$('[data-bind]', raiz).concat(raiz.hasAttribute('data-bind') ? [raiz] : []).forEach(function (n) {
      var v = dados[n.getAttribute('data-bind')];
      if (v != null) n.textContent = v;
    });
    MC.$$('[data-bind-attr]', raiz).concat(raiz.hasAttribute('data-bind-attr') ? [raiz] : []).forEach(function (n) {
      n.getAttribute('data-bind-attr').split(',').forEach(function (par) {
        var p = par.split(':'), v = dados[p[1]];
        if (v != null) n.setAttribute(p[0], v);
      });
    });
    return raiz;
  };

  MC.toast = function (mensagem, tipo) {
    var area = document.getElementById('toastArea');
    if (!area) {
      area = MC.el('div', { class: 'toast-area', id: 'toastArea', 'aria-live': 'polite' });
      document.body.appendChild(area);
    }
    var icone = { erro: 'fa-circle-exclamation', info: 'fa-circle-info' }[tipo] || 'fa-circle-check';
    var t = MC.el('div', { class: 'mc-toast ' + (tipo || ''), role: tipo === 'erro' ? 'alert' : 'status' }, [
      MC.el('i', { class: 'fa-solid ' + icone + ' mt-1' }), MC.el('span', { text: mensagem })
    ]);
    area.appendChild(t);
    setTimeout(function () { t.remove(); }, 5000);
  };

  MC.params = function () { return new URLSearchParams(window.location.search); };

  /** Cartão de médico (usa o <template id="tpl-medico-card"> de components/templates.html). */
  MC.cartaoMedico = function (m) {
    var no = MC.modelo('tpl-medico-card'), prox = m.proximo_horario;
    MC.preencher(no, {
      iniciais: m.iniciais, avatar_estilo: 'background:' + m.cor,
      nome: m.nome, url_perfil: 'medico.html?id=' + m.id,
      especialidade: m.especialidade, crm_texto: 'CRM-' + m.crm_uf + ' ' + m.crm,
      local: m.cidade + ' – ' + m.estado + ' · ' + m.bairro, endereco: m.endereco,
      modalidade_rotulo: MC.fmt.modalidade(m.modalidade),
      nota_texto: MC.fmt.nota(m.nota), avaliacoes_texto: '(' + m.total_avaliacoes + ')',
      proximo_texto: prox ? MC.fmt.horario(prox) : 'Sem horários nos próximos 30 dias',
      url_agendar: 'agendamento.html?medico_id=' + m.id + (prox ? '&horario=' + encodeURIComponent(prox.id) : '')
    });
    var presencial = m.modalidade === 'presencial';
    MC.$('[data-ref=badge]', no).classList.toggle('teal', !presencial);
    MC.$('[data-ref=badge-icone]', no).classList.add(presencial ? 'fa-building' : 'fa-video');
    if (!prox) MC.$('[data-ref=agendar]', no).classList.add('disabled');
    return no;
  };

  // ---------------------------------------------------------------- componentes
  // <div data-include="components/header.html"></div> é substituído pelo conteúdo do arquivo.
  function incluir() {
    var pendentes = MC.$$('[data-include]');
    if (!pendentes.length) return Promise.resolve();
    return Promise.all(pendentes.map(function (el) {
      return fetch(el.getAttribute('data-include')).then(function (r) {
        if (!r.ok) throw new Error('Componente não encontrado: ' + el.getAttribute('data-include'));
        return r.text();
      }).then(function (html) { el.outerHTML = html; });
    })).then(incluir);   // componentes podem incluir outros componentes
  }
  function marcarMenu() {
    var atual = document.body.getAttribute('data-pagina');
    MC.$$('[data-nav]').forEach(function (a) {
      var ativo = a.getAttribute('data-nav') === atual;
      a.classList.toggle('active', ativo);
      if (ativo) a.setAttribute('aria-current', 'page');
    });
  }

  /** Promessa resolvida quando header/footer/templates já estão na página. */
  MC.pronto = new Promise(function (ok) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ok); else ok();
  }).then(incluir).then(marcarMenu).catch(function (e) { console.error(e); });
})();
