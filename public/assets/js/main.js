
/* =========================================================
   MedCare — funções globais
   Includes, notificações, máscaras e validações
   ========================================================= */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', iniciar);

  async function iniciar() {
    await carregarIncludes();
    configurarMascaras();
    configurarFormularios();
    configurarLinksDeFase();
    marcarNavegacao();
  }

  // ---------- Carregar cabeçalho, rodapé e componentes ----------
  async function carregarIncludes() {
    const elementos = document.querySelectorAll('[data-include]');

    for (const elemento of elementos) {
      const caminho = elemento.getAttribute('data-include');

      if (!caminho) continue;

      try {
        const resposta = await fetch(caminho, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!resposta.ok) {
          throw new Error('HTTP ' + resposta.status);
        }

        elemento.innerHTML = await resposta.text();
      } catch (erro) {
        console.error('Erro ao carregar componente:', caminho, erro);
      }
    }

    // Os links do cabeçalho só existem depois do include.
    marcarNavegacao();
  }

  // ---------- Notificações ----------
  window.mcToast = function (mensagem, tipo = '') {
    let area = document.getElementById('toastArea');

    if (!area) {
      area = document.createElement('div');
      area.id = 'toastArea';
      area.className = 'toast-area';
      area.setAttribute('aria-live', 'polite');
      area.setAttribute('aria-atomic', 'false');
      document.body.appendChild(area);
    }

    const toast = document.createElement('div');
    toast.className = 'mc-toast ' + tipo;
    toast.setAttribute('role', tipo === 'erro' ? 'alert' : 'status');

    const icone = document.createElement('i');
    icone.className = 'fa-solid ' + (
      tipo === 'erro'
        ? 'fa-circle-exclamation'
        : tipo === 'info'
          ? 'fa-circle-info'
          : 'fa-circle-check'
    );

    icone.classList.add('mt-1');

    const texto = document.createElement('span');
    texto.textContent = mensagem;

    toast.append(icone, texto);
    area.appendChild(toast);

    window.setTimeout(() => toast.remove(), 5000);
  };

  // ---------- Máscaras ----------
  const mascaras = {
    cpf(valor) {
      valor = valor.replace(/\D/g, '').slice(0, 11);

      valor = valor.replace(/^(\d{3})(\d)/, '$1.$2');
      valor = valor.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
      valor = valor.replace(/\.(\d{3})(\d{1,2})$/, '.$1-$2');

      return valor;
    },

    telefone(valor) {
      valor = valor.replace(/\D/g, '').slice(0, 11);

      if (valor.length <= 2) {
        return valor ? '(' + valor : '';
      }

      if (valor.length <= 6) {
        return '(' + valor.slice(0, 2) + ') ' + valor.slice(2);
      }

      if (valor.length <= 10) {
        return valor.replace(
          /^(\d{2})(\d{4})(\d{0,4})$/,
          '($1) $2-$3'
        ).replace(/-$/, '');
      }

      return valor.replace(
        /^(\d{2})(\d{5})(\d{0,4})$/,
        '($1) $2-$3'
      ).replace(/-$/, '');
    }
  };

  function configurarMascaras() {
    document.querySelectorAll('[data-mascara]').forEach((campo) => {
      campo.addEventListener('input', function () {
        const tipo = campo.dataset.mascara;

        if (mascaras[tipo]) {
          campo.value = mascaras[tipo](campo.value);
        }
      });
    });
  }

  // ---------- Validar CPF ----------
  function cpfValido(valor) {
    const cpf = valor.replace(/\D/g, '');

    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
      return false;
    }

    for (let tamanho = 9; tamanho <= 10; tamanho++) {
      let soma = 0;

      for (let i = 0; i < tamanho; i++) {
        soma += Number(cpf[i]) * (tamanho + 1 - i);
      }

      const digito = (soma * 10 % 11) % 10;

      if (digito !== Number(cpf[tamanho])) {
        return false;
      }
    }

    return true;
  }

  // ---------- Validar campos ----------
  function validarCampo(campo, formulario) {
    let valido = campo.checkValidity();

    if (valido && campo.dataset.mascara === 'cpf') {
      valido = cpfValido(campo.value);
    }

    if (valido && campo.dataset.mascara === 'telefone') {
      valido = campo.value.replace(/\D/g, '').length >= 10;
    }

    if (valido && campo.id === 'senha2') {
      const senha = formulario.querySelector('#senha');
      valido = !!senha && campo.value === senha.value;
    }

    campo.classList.toggle('is-invalid', !valido);
    campo.setAttribute('aria-invalid', String(!valido));

    return valido;
  }

  function configurarFormularios() {
    ['form-login', 'form-cadastro'].forEach((id) => {
      const formulario = document.getElementById(id);

      if (!formulario) return;

      const campos = formulario.querySelectorAll(
        'input:not([type="hidden"]), select, textarea'
      );

      campos.forEach((campo) => {
        campo.addEventListener('blur', () => {
          if (campo.value.trim() !== '') {
            validarCampo(campo, formulario);
          }
        });

        campo.addEventListener('input', () => {
          if (campo.classList.contains('is-invalid')) {
            validarCampo(campo, formulario);
          }
        });
      });

      formulario.addEventListener('submit', (evento) => {
        let tudoValido = true;
        let primeiroInvalido = null;

        campos.forEach((campo) => {
          if (!validarCampo(campo, formulario)) {
            tudoValido = false;
            primeiroInvalido ||= campo;
          }
        });

        if (!tudoValido) {
          evento.preventDefault();
          primeiroInvalido?.focus();
          return;
        }

        // Não impede o envio normal para o PHP.
        // O backend deve validar os dados novamente.
      });
    });
  }

  // ---------- Links de funcionalidades futuras ----------
  function configurarLinksDeFase() {
    document.querySelectorAll('[data-fase-link]').forEach((link) => {
      link.addEventListener('click', (evento) => {
        evento.preventDefault();

        window.mcToast(
          link.dataset.faseLink || 'Funcionalidade em desenvolvimento.',
          'info'
        );
      });
    });
  }

  // ---------- Destacar página atual ----------
  function marcarNavegacao() {
    const pagina = document.body.dataset.pagina;

    if (!pagina) return;

    document.querySelectorAll('[data-nav]').forEach((link) => {
      const ativo = link.dataset.nav === pagina;

      link.classList.toggle('active', ativo);

      if (ativo) {
        link.setAttribute('aria-current', 'page');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  }
})();
