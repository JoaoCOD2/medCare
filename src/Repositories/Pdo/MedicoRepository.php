<?php
declare(strict_types=1);

namespace MedCare\Repositories\Pdo;

use MedCare\Repositories\Contracts\MedicoRepositoryInterface;
use PDO;

class MedicoRepository implements MedicoRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * SELECT base: traz o médico + nome da especialidade + modalidade.
     * Usado por todas as consultas, para o resultado ter sempre as mesmas chaves.
     */
    private function selectBase(): string
    {
        return "
            SELECT
                m.*,
                e.nome AS especialidade,
                CASE
                    WHEN m.atendimento = 'teleconsulta' THEN 'online'
                    ELSE m.atendimento
                END AS modalidade
            FROM medicos m
            LEFT JOIN especialidades e
                ON e.id = m.especialidade_id
        ";
    }

    /**
     * O PDO devolve DECIMAL como texto ('0.0'). Como as páginas usam
     * strict_types, funções como number_format() exigem número de verdade.
     */
    private function ajustar(array $m): array
    {
        // Valores padrão para colunas que podem não existir no banco ainda,
        // assim as páginas não quebram com "Undefined array key".
        $m += [
            'crm_uf'           => 'SP',
            'estado'           => 'SP',
            'clinica'          => '',
            'endereco'         => '',
            'valor_consulta'   => 0.0,
            'nota'             => 0.0,
            'total_avaliacoes' => 0,
            'biografia'        => '',
        ];

        // O PDO devolve DECIMAL como texto: converte para número de verdade.
        $m['nota']             = (float) $m['nota'];
        $m['valor_consulta']   = (float) $m['valor_consulta'];
        $m['total_avaliacoes'] = (int) $m['total_avaliacoes'];
        $m['biografia']        = (string) $m['biografia'];

        // Formação e experiência: as páginas esperam uma lista (array).
        $m['formacao']    = $this->paraLista($m['formacao'] ?? null);
        $m['experiencia'] = $this->paraLista($m['experiencia'] ?? null);
        $m['convenios']   = $this->paraLista($m['convenios'] ?? null);

        return $m;
    }

    /** Aceita JSON (["a","b"]) ou texto com um item por linha; devolve sempre um array. */
    private function paraLista($valor): array
    {
        if (is_array($valor)) {
            return $valor;
        }
        $texto = trim((string) $valor);
        if ($texto === '') {
            return [];
        }
        $json = json_decode($texto, true);
        if (is_array($json)) {
            return $json;
        }
        $linhas = preg_split('/\R+/', $texto) ?: [];
        return array_values(array_filter(array_map('trim', $linhas), fn($l) => $l !== ''));
    }

    private function ajustarLista(array $linhas): array
    {
        return array_map(fn(array $m) => $this->ajustar($m), $linhas);
    }

    public function listarTodos(): array
    {
        $stmt = $this->pdo->query($this->selectBase() . " ORDER BY m.nome ASC");
        return $this->ajustarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function todos(): array
    {
        return $this->listarTodos();
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->porId($id);
    }

    public function cidades(): array
    {
        $stmt = $this->pdo->query("SELECT DISTINCT cidade FROM medicos ORDER BY cidade ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function buscar(array $filtros = []): array
    {
        $sql = $this->selectBase() . " WHERE 1 = 1";
        $params = [];

        if (!empty($filtros['nome'])) {
            $sql .= " AND m.nome LIKE ?";
            $params[] = '%' . $filtros['nome'] . '%';
        }

        if (!empty($filtros['cidade'])) {
            $sql .= " AND m.cidade = ?";
            $params[] = $filtros['cidade'];
        }

        if (!empty($filtros['especialidade'])) {
            $sql .= " AND m.especialidade_id = ?";
            $params[] = $filtros['especialidade'];
        }

        $sql .= " ORDER BY m.nome ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $this->ajustarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->pdo->prepare($this->selectBase() . " WHERE m.id = ?");
        $stmt->execute([$id]);
        $m = $stmt->fetch(PDO::FETCH_ASSOC);
        return $m ? $this->ajustar($m) : null;
    }

    public function destaques(int $limite = 4): array
    {
        $stmt = $this->pdo->prepare($this->selectBase() . " ORDER BY m.nome ASC LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $this->ajustarLista($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function avaliacoes(int $medicoId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM avaliacoes WHERE medico_id = ? ORDER BY id DESC");
            $stmt->execute([$medicoId]);
            return array_map(function (array $a): array {
                // Nomes alternativos usados pelas páginas: garante que todos existam.
                $autor = (string) ($a['autor'] ?? $a['paciente'] ?? $a['paciente_nome'] ?? $a['nome'] ?? 'Paciente');
                $texto = (string) ($a['comentario'] ?? $a['texto'] ?? $a['mensagem'] ?? '');
                $data  = (string) ($a['data'] ?? $a['criado_em'] ?? '');
                $a['autor'] = $a['paciente'] = $a['paciente_nome'] = $a['nome'] = $autor;
                $a['comentario'] = $a['texto'] = $a['mensagem'] = $texto;
                $a['data'] = $a['criado_em'] = substr($data, 0, 10);
                $a['nota'] = (int) ($a['nota'] ?? 5);
                return $a;
            }, $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (\PDOException $e) {
            // Tabela "avaliacoes" ainda não existe no banco: mostra o médico sem avaliações.
            return [];
        }
    }
}