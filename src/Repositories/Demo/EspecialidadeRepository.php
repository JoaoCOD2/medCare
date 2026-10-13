<?php

declare(strict_types=1);

namespace MedCare\Repositories\Demo;

use MedCare\Demo\DemoData;
use MedCare\Repositories\Contracts\EspecialidadeRepositoryInterface;

final class EspecialidadeRepository implements EspecialidadeRepositoryInterface
{
    public function listarTodas(): array
    {
        return [
            [
                'id' => 1,
                'nome' => 'Cardiologia',
                'descricao' => 'Coração e sistema circulatório',
                'icone' => 'fa-heart-pulse',
                'slug' => 'cardiology',
                'total_medicos' => 3
            ],
            [
                'id' => 2,
                'nome' => 'Dermatologia',
                'descricao' => 'Pele, cabelos e unhas',
                'icone' => 'fa-droplet',
                'slug' => 'dermatology',
                'total_medicos' => 2
            ],
            [
                'id' => 3,
                'nome' => 'Pediatria',
                'descricao' => 'Saúde de bebês, crianças e adolescentes',
                'icone' => 'fa-baby',
                'slug' => 'pediatrics',
                'total_medicos' => 4
            ],
            [
                'id' => 4,
                'nome' => 'Ortopedia',
                'descricao' => 'Ossos, articulações e músculos',
                'icone' => 'fa-bone',
                'slug' => 'orthopedics',
                'total_medicos' => 1
            ],
            [
                'id' => 5,
                'nome' => 'Ginecologia',
                'descricao' => 'Saúde da mulher',
                'icone' => 'fa-venus',
                'slug' => 'gynecology',
                'total_medicos' => 5
            ],
            [
                'id' => 6,
                'nome' => 'Oftalmologia',
                'descricao' => 'Visão e saúde dos olhos',
                'icone' => 'fa-eye',
                'slug' => 'ophthalmology',
                'total_medicos' => 2
            ],
            [
                'id' => 7,
                'nome' => 'Neurologia',
                'descricao' => 'Cérebro e sistema nervoso',
                'icone' => 'fa-brain',
                'slug' => 'neurology',
                'total_medicos' => 1
            ],
            [
                'id' => 8,
                'nome' => 'Psiquiatria',
                'descricao' => 'Saúde mental e emocional',
                'icone' => 'fa-comments',
                'slug' => 'psychiatry',
                'total_medicos' => 3
            ],
            [
                'id' => 9,
                'nome' => 'Clínica Geral',
                'descricao' => 'Avaliação geral e check-ups',
                'icone' => 'fa-stethoscope',
                'slug' => 'general',
                'total_medicos' => 6
            ],
            [
                'id' => 10,
                'nome' => 'Endocrinologia',
                'descricao' => 'Hormônios e metabolismo',
                'icone' => 'fa-vial',
                'slug' => 'endocrinology',
                'total_medicos' => 2
            ],
            [
                'id' => 11,
                'nome' => 'Nutrição',
                'descricao' => 'Alimentação e reeducação alimentar',
                'icone' => 'fa-apple-whole',
                'slug' => 'nutrition',
                'total_medicos' => 4
            ],
            [
                'id' => 12,
                'nome' => 'Odontologia',
                'descricao' => 'Dentes e saúde bucal',
                'icone' => 'fa-tooth',
                'slug' => 'dentistry',
                'total_medicos' => 3
            ]
        ];
    }

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
        foreach ($this->todas() as $especialidade) {
            if ($especialidade['id'] === $id) {
                return $especialidade;
            }
        }
 
        return null;
    }
}
