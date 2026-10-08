<?php /** Variável opcional: $extraJs (array de caminhos dentro de assets/js). */ ?>
</main>

<footer class="mc-footer" id="contato">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <?php include __DIR__ . '/partials/logo.php'; ?>
        <p class="mt-3">Cuidado e tecnologia para aproximar você da saúde.</p>
        <div class="social" aria-label="Redes sociais">
          <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
          <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h4>Navegue</h4>
        <ul>
          <li><a href="<?= e(url('index.php')) ?>">Início</a></li>
          <li><a href="<?= e(url('pages/medicos.php')) ?>">Encontrar Médico</a></li>
          <li><a href="<?= e(url('pages/especialidades.php')) ?>">Especialidades</a></li>
          <li><a href="<?= e(url('index.php')) ?>#sobre">Sobre</a></li>
          <li><a href="<?= e(url('index.php')) ?>#contato">Contato</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-3">
        <h4>Atendimento</h4>
        <ul>
          <li><i class="fa-solid fa-phone me-2"></i><?= e(config('contato.telefone')) ?></li>
          <li><i class="fa-regular fa-envelope me-2"></i><a href="mailto:<?= e(config('contato.email')) ?>"><?= e(config('contato.email')) ?></a></li>
          <li><i class="fa-regular fa-clock me-2"></i><?= e(config('contato.horario')) ?></li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h4>Institucional</h4>
        <ul>
          <li><a href="#">Termos de Uso</a></li>
          <li><a href="#">Política de Privacidade</a></li>
          <li><a href="#">Política de Cookies</a></li>
        </ul>
      </div>
    </div>
    <div class="copy">© <?= date('Y') ?> MedCare. Todos os direitos reservados.</div>
  </div>
</footer>

<div class="toast-area" id="toastArea" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/main.js')) ?>"></script>
<?php foreach (($extraJs ?? []) as $js): ?>
  <script src="<?= e(asset($js)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
