<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';

use MedCare\Core\Container;
use MedCare\Services\BuscaService;

$especialidades = Container::especialidades()->todas();
$destaques      = (new BuscaService())->buscar([])['medicos'];
$destaques      = array_slice($destaques, 0, 4);
$cidades        = Container::medicos()->cidades();

// Cartão "agenda ao vivo" do hero: usa o 1º destaque e seus horários reais.
$vivo      = $destaques[0] ?? null;
$vivoDias  = $vivo ? array_slice(Container::horarios()->dias($vivo['id'], 5), 0, 5) : [];
$vivoSlots = [];
if ($vivoDias) {
    $vivoSlots = array_slice(Container::horarios()->doDia($vivo['id'], $vivoDias[0]['data']), 0, 6);
}
$primeiroLivre = null;
foreach ($vivoSlots as $s) { if ($s['status'] === 'disponivel') { $primeiroLivre = $s['id']; break; } }

$titulo = null;
$pagina = 'inicio';
include dirname(__DIR__) . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero" aria-labelledby="hero-titulo">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <h1 id="hero-titulo">Encontre o médico ideal para você</h1>
        <p class="lead">Agende sua consulta de forma rápida, segura e sem complicação.</p>

        <form class="busca-hero" action="<?= e(url('pages/medicos.php')) ?>" method="get" role="search">
          <div class="campo">
            <label for="h-q"><i class="fa-solid fa-magnifying-glass"></i>Especialidade ou médico</label>
            <input id="h-q" name="q" type="text" placeholder="Ex.: Cardiologia, Dra. Marina" autocomplete="off">
          </div>
          <div class="campo">
            <label for="h-cidade"><i class="fa-solid fa-location-dot"></i>Cidade</label>
            <input id="h-cidade" name="cidade" type="text" list="lista-cidades" placeholder="Porto Alegre">
            <datalist id="lista-cidades">
              <?php foreach ($cidades as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
            </datalist>
          </div>
          <div class="campo">
            <label for="h-data"><i class="fa-regular fa-calendar"></i>Data</label>
            <input id="h-data" name="data" type="date" min="<?= e(date('Y-m-d')) ?>">
          </div>
          <button class="btn btn-mc" type="submit">Buscar médicos</button>
        </form>

        <div class="hero-confianca">
          <span><i class="fa-solid fa-shield-heart"></i>Dados protegidos</span>
          <span><i class="fa-solid fa-bolt"></i>Confirmação imediata</span>
          <span><i class="fa-solid fa-video"></i>Presencial ou online</span>
        </div>
      </div>

      <?php if ($vivo && $vivoDias): ?>
      <div class="col-lg-5">
        <div class="agenda-viva" aria-label="Exemplo de agenda disponível">
          <div class="topo">
            <div class="avatar" style="background:<?= e(cor_avatar($vivo['nome'])) ?>" aria-hidden="true"><?= e(iniciais($vivo['nome'])) ?></div>
            <div>
              <div class="nome"><?= e($vivo['nome']) ?></div>
              <div class="esp"><?= e($vivo['especialidade']) ?> · <span class="nota"><i class="fa-solid fa-star"></i><?= number_format($vivo['nota'], 1, ',', '') ?></span></div>
            </div>
          </div>
          <div class="dias">
            <?php foreach ($vivoDias as $i => $d): ?>
              <div class="dia-chip <?= $i === 0 ? 'ativo' : '' ?>"><?= e(dia_semana_curto($d['data'])) ?><b><?= e(date('d', strtotime($d['data']))) ?></b></div>
            <?php endforeach; ?>
          </div>
          <div class="horas">
            <?php foreach ($vivoSlots as $s): ?>
              <?php if ($s['status'] === 'disponivel'): ?>
                <a class="slot <?= $s['id'] === $primeiroLivre ? 'destaque' : '' ?>"
                   href="<?= e(url('pages/agendamento.php', ['medico_id' => $vivo['id'], 'horario' => $s['id']])) ?>"><?= e(hora_curta($s['hora_inicio'])) ?></a>
              <?php else: ?>
                <span class="slot ocupado" aria-label="Horário ocupado"><?= e(hora_curta($s['hora_inicio'])) ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <div class="selo"><i class="fa-solid fa-circle-check"></i>Horários atualizados em tempo real</div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- SERVIÇOS -->
<section class="secao" aria-labelledby="servicos-titulo">
  <div class="container">
    <div class="secao-titulo">
      <h2 id="servicos-titulo">Cuide da sua saúde em poucos passos</h2>
      <p>Tudo o que você precisa para encontrar, agendar e acompanhar seu atendimento.</p>
    </div>
    <div class="grade-servicos">
      <a class="servico grande escuro" href="<?= e(url('pages/medicos.php')) ?>">
        <span class="icone"><i class="fa-solid fa-user-doctor"></i></span>
        <h3>Encontrar Médico</h3>
        <p>Encontre profissionais por nome, especialidade ou localização.</p>
        <span class="mais">Buscar médicos</span>
      </a>
      <a class="servico grande teal" href="<?= e(url('pages/agendamento.php')) ?>">
        <span class="icone"><i class="fa-regular fa-calendar-check"></i></span>
        <h3>Agendar Consulta</h3>
        <p>Escolha o melhor dia e horário para seu atendimento.</p>
        <span class="mais">Agendar agora</span>
      </a>
      <a class="servico" href="<?= e(url('pages/medicos.php', ['atendimento' => 'online'])) ?>">
        <span class="icone"><i class="fa-solid fa-video"></i></span>
        <h3>Telemedicina</h3>
        <p>Consulte a possibilidade de atendimento online.</p>
        <span class="mais">Ver médicos online</span>
      </a>
      <a class="servico" href="<?= e(url('pages/consultas.php')) ?>">
        <span class="icone"><i class="fa-regular fa-calendar"></i></span>
        <h3>Minha Agenda</h3>
        <p>Visualize e gerencie suas consultas.</p>
        <span class="mais">Abrir agenda</span>
      </a>
      <a class="servico" href="#faq">
        <span class="icone"><i class="fa-regular fa-file-lines"></i></span>
        <h3>Resultados e Documentos</h3>
        <p>Área preparada para futuramente disponibilizar documentos médicos.</p>
        <span class="mais">Saiba mais</span>
      </a>
      <a class="servico" href="#contato">
        <span class="icone"><i class="fa-solid fa-headset"></i></span>
        <h3>Atendimento</h3>
        <p>Encontre canais de contato e suporte.</p>
        <span class="mais">Falar com a gente</span>
      </a>
    </div>
  </div>
</section>

<!-- ESPECIALIDADES -->
<section class="secao secao-mint" aria-labelledby="esp-titulo">
  <div class="container">
    <div class="secao-titulo">
      <h2 id="esp-titulo">Encontre atendimento por especialidade</h2>
    </div>
    <div class="grade-esp">
      <?php foreach ($especialidades as $esp): ?>
        <a class="esp" href="<?= e(url('pages/medicos.php', ['especialidade' => $esp['id']])) ?>">
          <i class="fa-solid <?= e($esp['icone']) ?>" aria-hidden="true"></i>
          <b><?= e($esp['nome']) ?></b>
          <small><?= (int) $esp['total_medicos'] ?> <?= $esp['total_medicos'] === 1 ? 'profissional' : 'profissionais' ?></small>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="mt-4">
      <a class="btn btn-mc-outline" href="<?= e(url('pages/especialidades.php')) ?>">Ver todas as especialidades</a>
    </div>
  </div>
</section>

<!-- MÉDICOS EM DESTAQUE -->
<section class="secao" aria-labelledby="dest-titulo">
  <div class="container">
    <div class="secao-titulo d-flex justify-content-between align-items-end flex-wrap gap-3">
      <div>
        <h2 id="dest-titulo">Médicos mais bem avaliados</h2>
        <p>Profissionais com os melhores comentários de pacientes.</p>
      </div>
      <a class="btn btn-mc-outline" href="<?= e(url('pages/medicos.php')) ?>">Ver todos os médicos</a>
    </div>
    <div class="row g-4">
      <?php foreach ($destaques as $m): ?>
        <div class="col-md-6 col-xl-3"><?php include dirname(__DIR__) . '/includes/partials/medico_card.php'; ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="secao secao-mint" id="como-funciona" aria-labelledby="como-titulo">
  <div class="container">
    <div class="secao-titulo"><h2 id="como-titulo">Como funciona</h2></div>
    <div class="passos">
      <div class="passo"><h3>Encontre seu médico</h3><p>Busque por especialidade, nome ou cidade e compare avaliações.</p></div>
      <div class="passo"><h3>Escolha o horário</h3><p>Veja a agenda em tempo real e selecione o melhor dia.</p></div>
      <div class="passo"><h3>Confirme a consulta</h3><p>Revise o resumo e receba o número da sua consulta na hora.</p></div>
      <div class="passo"><h3>Compareça ao atendimento</h3><p>Vá ao consultório ou entre na consulta online no horário marcado.</p></div>
    </div>
  </div>
</section>

<!-- TELEMEDICINA -->
<section class="secao" aria-labelledby="tele-titulo">
  <div class="container">
    <div class="tele">
      <div class="row align-items-center g-5">
        <div class="col-lg-7">
          <h2 id="tele-titulo">Consulte seu médico de onde estiver</h2>
          <p>Vários profissionais atendem por vídeo. Você economiza deslocamento e mantém o mesmo acompanhamento.</p>
          <ul>
            <li><i class="fa-solid fa-check"></i>Retornos e acompanhamentos sem sair de casa</li>
            <li><i class="fa-solid fa-check"></i>Escolha entre presencial e online ao agendar</li>
            <li><i class="fa-solid fa-check"></i>Mesma agenda, mesmo número de consulta</li>
          </ul>
          <a class="btn btn-claro btn-lg" href="<?= e(url('pages/medicos.php', ['atendimento' => 'online'])) ?>">Buscar atendimento online</a>
        </div>
        <div class="col-lg-5" aria-hidden="true">
          <div class="tele-tela">
            <div class="video">
              <div class="avatar">TV</div>
              <div class="minha"><i class="fa-solid fa-user"></i></div>
            </div>
            <div class="controles">
              <span><i class="fa-solid fa-microphone"></i></span>
              <span><i class="fa-solid fa-video"></i></span>
              <span class="fim"><i class="fa-solid fa-phone-slash"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SOBRE -->
<section class="secao pt-0" id="sobre" aria-labelledby="sobre-titulo">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6">
        <h2 id="sobre-titulo">Sobre a MedCare</h2>
        <p class="mt-3">A MedCare aproxima pacientes e médicos com uma agenda clara, transparente e fácil de usar. Nosso trabalho é tirar o atrito entre você e o cuidado de que precisa.</p>
      </div>
      <div class="col-lg-6">
        <div class="principios">
          <div class="principio"><i class="fa-solid fa-eye"></i><div><b>Transparência</b><span>Horários reais, valores e convênios à vista antes de agendar.</span></div></div>
          <div class="principio"><i class="fa-solid fa-hand-pointer"></i><div><b>Praticidade</b><span>Da busca à confirmação em poucos cliques, no celular ou no computador.</span></div></div>
          <div class="principio"><i class="fa-solid fa-lock"></i><div><b>Privacidade</b><span>Seus dados são tratados com segurança e conforme a LGPD.</span></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="secao secao-mint" id="faq" aria-labelledby="faq-titulo">
  <div class="container" style="max-width: 860px;">
    <div class="secao-titulo"><h2 id="faq-titulo">Perguntas frequentes</h2></div>
    <div class="accordion" id="faqLista">
      <?php
      $faq = [
        ['Preciso criar uma conta para agendar?', 'Você pode pesquisar médicos e ver horários sem conta. Para confirmar uma consulta, é preciso entrar ou criar uma conta gratuita.'],
        ['Como cancelo uma consulta?', 'Em Minhas consultas, escolha a consulta e clique em Cancelar consulta. O horário volta para a agenda do médico. Consultas já concluídas não podem ser canceladas.'],
        ['Posso escolher entre presencial e online?', 'Sim, quando o médico oferece as duas modalidades. A opção aparece na etapa de agendamento.'],
        ['Meus dados estão seguros?', 'Sim. As senhas são armazenadas com criptografia (hash) e o acesso às informações respeita o tipo de usuário: paciente, médico ou administrador.'],
        ['Os convênios aparecem no perfil?', 'Cada médico mostra os convênios que aceita e o valor da consulta particular.'],
        ['E se não houver horário na data que eu quero?', 'Use a busca com data para ver quem tem vaga naquele dia, ou veja o próximo horário disponível em cada cartão.'],
      ];
      foreach ($faq as $i => [$p, $r]): ?>
        <div class="accordion-item">
          <h3 class="accordion-header">
            <button class="accordion-button <?= $i ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse"
                    data-bs-target="#faq<?= $i ?>" aria-expanded="<?= $i ? 'false' : 'true' ?>" aria-controls="faq<?= $i ?>"><?= e($p) ?></button>
          </h3>
          <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i ? '' : 'show' ?>" data-bs-parent="#faqLista">
            <div class="accordion-body"><?= e($r) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="secao" aria-labelledby="cta-titulo">
  <div class="container">
    <div class="cta">
      <h2 id="cta-titulo">Pronto para cuidar da sua saúde?</h2>
      <p>Encontre um médico e escolha seu horário em menos de dois minutos.</p>
      <a class="btn btn-claro btn-lg" href="<?= e(url('pages/agendamento.php')) ?>">Agendar consulta</a>
    </div>
  </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
