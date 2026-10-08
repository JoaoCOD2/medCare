<?php
declare(strict_types=1);

/**
 * Ponto único de inicialização. Toda página/endpoint começa com:
 *   require dirname(__DIR__, 2) . '/src/bootstrap.php';
 */

define('ROOT_PATH', dirname(__DIR__));

// Autoload PSR-4 simples: MedCare\Foo\Bar -> src/Foo/Bar.php
spl_autoload_register(static function (string $classe): void {
    $prefixo = 'MedCare\\';
    if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) {
        return;
    }
    $arquivo = ROOT_PATH . '/src/' . str_replace('\\', '/', substr($classe, strlen($prefixo))) . '.php';
    if (is_file($arquivo)) {
        require $arquivo;
    }
});

require ROOT_PATH . '/src/Helpers/functions.php';

$GLOBALS['medcare_config'] = require ROOT_PATH . '/config/app.php';
date_default_timezone_set(config('fuso'));

MedCare\Core\Session::iniciar();
