<?php
declare(strict_types=1);

namespace MedCare\Repositories\Demo;

use MedCare\Demo\DemoData;
use MedCare\Repositories\Contracts\EspecialidadeRepositoryInterface;

final class EspecialidadeRepository implements EspecialidadeRepositoryInterface
{
    public function todas(): array
    {
        $contagem = array_count_values(array_column(DemoData::medicos(), 'especialidade_id'));
        return array_map(static function (array $e) use ($contagem) {
            $e['total_medicos'] = $contagem[$e['id']] ?? 0;
            return $e;
        }, DemoData::especialidades());
    }

    public function porId(int $id): ?array
    {
        foreach ($this->todas() as $e) {
            if ($e['id'] === $id) {
                return $e;
            }
        }
        return null;
    }
}
