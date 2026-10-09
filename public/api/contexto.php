<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/src/bootstrap.php';

use MedCare\Core\{Auth, Csrf};

/** GET: dados globais da sessão (token CSRF e paciente atual). */
exigir_metodo('GET');
$p = Auth::pacienteAtual();
json_resposta([
    'ok'       => true,
    'nome'     => config('nome'),
    'csrf'     => Csrf::token(),
    'paciente' => ['id' => $p['id'], 'nome' => $p['nome'], 'email' => $p['email'], 'demo' => !empty($p['demo'])],
]);
