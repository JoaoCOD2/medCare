<?php
declare(strict_types=1);

/** Helpers globais de view/URL. Nada aqui acessa banco de dados. */

function config(string $chave, mixed $padrao = null): mixed
{
    $valor = $GLOBALS['medcare_config'] ?? [];
    foreach (explode('.', $chave) as $parte) {
        if (!is_array($valor) || !array_key_exists($parte, $valor)) {
            return $padrao;
        }
        $valor = $valor[$parte];
    }
    return $valor;
}

/** Escapa saída HTML (proteção contra XSS). Use em TODA variável impressa. */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $caminho = '', array $query = []): string
{
    $url = config('base_url', '') . '/' . ltrim($caminho, '/');
    $query = array_filter($query, static fn($v) => $v !== null && $v !== '');
    return $query ? $url . '?' . http_build_query($query) : $url;
}

function asset(string $caminho): string
{
    return url('assets/' . ltrim($caminho, '/'));
}

function redirecionar(string $destino): never
{
    header('Location: ' . $destino);
    exit;
}

function json_resposta(array $dados, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Valor anterior de um campo GET (mantém filtros preenchidos). */
function entrada(string $campo, string $padrao = ''): string
{
    $v = $_GET[$campo] ?? $padrao;
    return is_string($v) ? trim($v) : $padrao;
}

/** Minúsculas sem acentos, para buscas "tolerantes". */
function normalizar(string $texto): string
{
    $texto = mb_strtolower($texto, 'UTF-8');
    return strtr($texto, [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c',
    ]);
}

function iniciais(string $nome): string
{
    $nome = preg_replace('/^(dr|dra)\.?\s+/iu', '', trim($nome)) ?? $nome;
    $partes = preg_split('/\s+/', $nome) ?: [];
    $primeira = mb_substr($partes[0] ?? '', 0, 1);
    $ultima = count($partes) > 1 ? mb_substr(end($partes), 0, 1) : '';
    return mb_strtoupper($primeira . $ultima);
}

function moeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function dia_semana_curto(string $data): string
{
    $nomes = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];
    return $nomes[(int) date('w', strtotime($data))];
}

function dia_semana_longo(string $data): string
{
    $nomes = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
    return $nomes[(int) date('w', strtotime($data))];
}

function data_br(string $data): string
{
    return date('d/m/Y', strtotime($data));
}

function hora_curta(string $hora): string
{
    return substr($hora, 0, 5);
}

/** "Hoje às 15:30", "Amanhã às 09:00" ou "qui, 08/10 às 14:30". */
function rotulo_horario(array $horario): string
{
    $data = $horario['data'];
    $hora = hora_curta($horario['hora_inicio']);
    if ($data === date('Y-m-d')) {
        return "Hoje às {$hora}";
    }
    if ($data === date('Y-m-d', strtotime('+1 day'))) {
        return "Amanhã às {$hora}";
    }
    return mb_strtolower(dia_semana_curto($data)) . ', ' . date('d/m', strtotime($data)) . " às {$hora}";
}

function rotulo_modalidade(string $modalidade): string
{
    return match ($modalidade) {
        'presencial' => 'Presencial',
        'online'     => 'Online',
        default      => 'Presencial e online',
    };
}

function rotulo_status(string $status): string
{
    return match ($status) {
        'confirmada'  => 'Confirmada',
        'aguardando'  => 'Aguardando confirmação',
        'concluida'   => 'Concluída',
        'cancelada'   => 'Cancelada',
        default       => ucfirst($status),
    };
}

/** Cor estável do avatar a partir do nome (sem depender de fotos). */
function cor_avatar(string $nome): string
{
    $paleta = ['#0E7C57', '#0B6E7F', '#2C6E49', '#1D6F8A', '#3D7A5A', '#176B6B'];
    return $paleta[crc32($nome) % count($paleta)];
}
