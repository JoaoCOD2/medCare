
/* =========================================================
   MedCare — página inicial
   Especialidades e médicos em destaque
   ========================================================= */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', iniciarHome);

  function iniciarHome() {
    renderizarEspecialidades();
    prepararDestaques();
    configurarBuscaInicial();
    configurarDataMinima();
  }

  // ---------- Especialidades ----------
  const especialidades = [
    {
      nome: 'Cardiologia',
      icone: 'fa-solid fa-heart-pulse',
      descricao: 'Cuidados com o coração'
    },
    {
      nome: 'Dermatologia',
      icone: 'fa-solid fa-hand',
      descricao: 'Cuidados com a pele'
    },
    {
      nome: 'Pediatria',
      icone: 'fa-solid fa-baby',
      descricao: 'Saúde de crianças'
    },
    {
      nome: 'Ortopedia',
      icone: 'fa-solid fa-bone',
      descricao: 'Ossos e articulações'
    },
    {
      nome: 'Neurologia',
      icone: 'fa-solid fa-brain',
      descricao: 'Sistema nervoso'
    },
    {
      nome: 'Oftalmologia',
      icone: 'fa-solid fa-eye',
      descricao: 'Saúde dos olhos'
    },
    {
      nome: 'Ginecologia',
      icone: 'fa-solid fa-person-dress',
      descricao: 'Saúde da mulher'
    },
    {
      nome: 'Clínica Geral',
      icone: 'fa-solid fa-stethoscope',
      descricao: 'Cuidados gerais'
    }
  ];

  function renderizarEspecialidades() {
    const grade = document.getElementById('grade-esp');

    if (!grade) return;

    grade.replaceChildren();

    especialidades.forEach((especialidade) => {
      const link = document.createElement('a');
      link.className = 'esp';
      link.href = 'pages/medicos.php?q=' +
        encodeURIComponent(especialidade.nome);

      const icone = document.createElement('i');
      icone.className = especialidade.icone;
      icone.setAttribute('aria-hidden', 'true');

      const nome = document.createElement('b');
      nome.textContent = especialidade.nome;

      const descricao = document.createElement('small');
      descricao.textContent = especialidade.descricao;

      link.append(icone, nome, descricao);
      grade.appendChild(link);
    });
  }

  // ---------- Médicos em destaque ----------
  function prepararDestaques() {
    const grade = document.getElementById('destaques');

    if (!grade) return;

    /*
     * Os médicos devem vir do backend/banco de dados.
     * Não criamos profissionais fictícios.
     *
     * Quando a integração com o endpoint PHP estiver pronta,
     * os cards reais poderão ser inseridos nesta área.
     */

    const coluna = document.createElement('div');
    coluna.className = 'col-12';

    const vazio = document.createElement('div');
    vazio.className = 'vazio';

    const icone = document.createElement('i');
    icone.className = 'fa-solid fa-user-doctor';
    icone.setAttribute('aria-hidden', 'true');

    const titulo = document.createElement('h3');
    titulo.textContent = 'Encontre seu médico';

    const texto = document.createElement('p');
    texto.textContent =
      'Acesse a lista de profissionais para consultar especialidades e opções de atendimento.';

    const botao = document.createElement('a');
    botao.className = 'btn btn-mc';
    botao.href = 'pages/medicos.php';
    botao.textContent = 'Ver médicos';

    vazio.append(icone, titulo, texto, botao);
    coluna.appendChild(vazio);
    grade.appendChild(coluna);
  }

  // ---------- Busca da página inicial ----------
  function configurarBuscaInicial() {
    const formulario = document.querySelector('.busca-hero');

    if (!formulario) return;

    formulario.addEventListener('submit', (evento) => {
      const campoBusca = document.getElementById('h-q');
      const campoCidade = document.getElementById('h-cidade');

      if (campoBusca) {
        campoBusca.value = campoBusca.value.trim();
      }

      if (campoCidade) {
        campoCidade.value = campoCidade.value.trim();
      }

      // Permite o envio normal para pages/medicos.php.
      // Não usa preventDefault para não bloquear a pesquisa.
    });
  }

  // ---------- Impedir seleção de datas passadas ----------
  function configurarDataMinima() {
    const campoData = document.getElementById('h-data');

    if (!campoData) return;

    const agora = new Date();
    const ano = agora.getFullYear();
    const mes = String(agora.getMonth() + 1).padStart(2, '0');
    const dia = String(agora.getDate()).padStart(2, '0');

    campoData.min = `${ano}-${mes}-${dia}`;
  }
})();
