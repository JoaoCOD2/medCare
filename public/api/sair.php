<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_resposta(['ok' => false, 'erro' => 'Método não permitido.'], 405);
}

// Não depende de nenhum método extra: funciona com qualquer Session.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $c = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $c['path'] ?: '/',
        'domain'   => $c['domain'],
        'secure'   => (bool) $c['secure'],
        'httponly' => (bool) $c['httponly'],
        'samesite' => $c['samesite'] ?? 'Lax',
    ]);
}

session_destroy();

json_resposta(['ok' => true]);