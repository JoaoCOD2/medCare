<?php
declare(strict_types=1);

namespace MedCare\Repositories\Contracts;

interface EspecialidadeRepositoryInterface
{
    /** @return array[] id, nome, descricao, icone, total_medicos */
    public function todas(): array;

    public function porId(int $id): ?array;
}
