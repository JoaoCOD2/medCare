/* Tela de sucesso: mostra os dados da consulta recém-criada. */
MC.pronto.then(function () {
  'use strict';
  var numero = MC.params().get('n') || '';
  MC.api.get('api/consultas.php', { numero: numero }).then(function (r) {
    var c = r.consulta, m = c.medico;
    MC.$('#s-numero').textContent = c.numero;
    MC.$('#s-medico').textContent = m.nome;
    MC.$('#s-esp').textContent = m.especialidade;
    var longa = MC.fmt.dataLonga(c.data);
    MC.$('#s-data').textContent = MC.fmt.dataBr(c.data) + ' (' + longa.split(',')[0] + ')';
    MC.$('#s-hora').textContent = MC.fmt.hora(c.hora);
    MC.$('#s-tipo').textContent = MC.fmt.modalidade(c.tipo);
    MC.$('#s-local').textContent = c.tipo === 'online' ? 'Link de acesso enviado antes da consulta' : m.clinica + ' — ' + m.endereco;
    MC.$('#sucesso').hidden = false;
  }).catch(function () { window.location.replace('consultas.html'); });
});
