<?php
declare(strict_types=1);

namespace MedCare\Repositories\Contracts;

interface MedicoRepositoryInterface
{
    /**
     * Filtros aceitos (todos opcionais): q, especialidade_id, cidade, bairro,
     * atendimento ('presencial'|'online').
     * @return array[]
     */
    public function buscar(array $filtros = []): array;

    public function porId(int $id): ?array;

    /** @return array[] */
    public function destaques(int $limite = 4): array;

    /** @return string[] cidades com médicos ativos */
    public function cidades(): array;

    /** @return array[] nome, nota, comentario, data */
    public function avaliacoes(int $medicoId): array;
}
