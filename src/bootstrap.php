<?php declare(strict_types=1);
/**
 * Ponto único de inicialização.
 */
define('ROOT_PATH', dirname(__DIR__));

// Autoload PSR-4 simples para carregar os Repositórios automaticamente
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

// Carrega as funções globais (exigir_metodo, json_resposta, etc.)
require_once ROOT_PATH . '/src/Helpers/functions.php';

// Define o fuso horário do Brasil de forma direta e segura
date_default_timezone_set('America/Sao_Paulo');

// Inicializa a sessão segura do paciente
\MedCare\Core\Session::iniciar();
$GLOBALS['medcare_config'] = require ROOT_PATH . '/config/app.php';
