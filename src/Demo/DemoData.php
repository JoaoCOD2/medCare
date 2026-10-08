<?php
declare(strict_types=1);

namespace MedCare\Demo;

/**
 * DADOS DE DEMONSTRAÇÃO (Fase 1).
 * Isolados de propósito: quando o MySQL entrar, este arquivo e a pasta
 * Repositories/Demo podem ser removidos sem tocar no resto do sistema.
 * Os mesmos dados irão para database/seed.sql na Fase 2.
 */
final class DemoData
{
    public static function especialidades(): array
    {
        $linhas = [
            [1,  'Cardiologia',     'Coração e sistema circulatório',        'fa-heart-pulse'],
            [2,  'Dermatologia',    'Pele, cabelos e unhas',                  'fa-droplet'],
            [3,  'Pediatria',       'Saúde de bebês, crianças e adolescentes', 'fa-baby'],
            [4,  'Ortopedia',       'Ossos, articulações e músculos',         'fa-bone'],
            [5,  'Ginecologia',     'Saúde da mulher',                        'fa-venus'],
            [6,  'Oftalmologia',    'Visão e saúde dos olhos',                'fa-eye'],
            [7,  'Neurologia',      'Cérebro e sistema nervoso',              'fa-brain'],
            [8,  'Psiquiatria',     'Saúde mental e emocional',               'fa-comments'],
            [9,  'Clínica Geral',   'Avaliação geral e check-ups',            'fa-stethoscope'],
            [10, 'Endocrinologia',  'Hormônios e metabolismo',                'fa-vial'],
            [11, 'Nutrição',        'Alimentação e reeducação alimentar',     'fa-apple-whole'],
            [12, 'Odontologia',     'Dentes e saúde bucal',                   'fa-tooth'],
        ];
        return array_map(static fn($l) => [
            'id' => $l[0], 'nome' => $l[1], 'descricao' => $l[2], 'icone' => $l[3], 'ativo' => 1,
        ], $linhas);
    }

    public static function medicos(): array
    {
        // id, nome, esp, crm, cidade, bairro, endereço, clínica, modalidade, valor, nota, nº aval., convênios, bio, formação, experiência
        $m = [
            [1, 'Dr. Carlos Almeida', 1, '12345', 'Porto Alegre', 'Moinhos de Vento', 'Rua Padre Chagas, 120 – sala 304', 'Clínica MedCare Moinhos', 'ambos', 380, 4.9, 187,
             ['Unimed Exemplo', 'Bradesco Saúde', 'Particular'],
             'Cardiologista com foco em prevenção, hipertensão e saúde do coração do atleta. Atende com tempo para ouvir e explica cada exame em linguagem simples.',
             ['Medicina – UFRGS', 'Residência em Cardiologia – Hospital de Clínicas de Porto Alegre', 'Pós-graduação em Ecocardiografia'],
             ['16 anos de prática clínica', 'Ex-coordenador do ambulatório de hipertensão', 'Membro da Sociedade Brasileira de Cardiologia']],
            [2, 'Dra. Marina Duarte', 2, '23456', 'Porto Alegre', 'Petrópolis', 'Av. Protásio Alves, 890 – sala 12', 'Clínica MedCare Petrópolis', 'presencial', 320, 4.8, 142,
             ['Unimed Exemplo', 'Amil', 'Particular'],
             'Dermatologista clínica e estética. Trata acne, dermatites, manchas e faz o acompanhamento de pintas com dermatoscopia digital.',
             ['Medicina – UFCSPA', 'Residência em Dermatologia – Santa Casa de Porto Alegre'],
             ['11 anos de atuação', 'Especialização em dermatoscopia', 'Palestrante em congressos regionais']],
            [3, 'Dra. Beatriz Lemos', 3, '34567', 'Canoas', 'Centro', 'Rua Quinze de Janeiro, 455', 'Espaço Infantil MedCare', 'presencial', 290, 5.0, 214,
             ['Unimed Exemplo', 'SulAmérica', 'Particular'],
             'Pediatra com atendimento acolhedor para crianças e famílias, do pré-natal pediátrico à adolescência. Orientação de aleitamento, vacinação e desenvolvimento.',
             ['Medicina – PUCRS', 'Residência em Pediatria – Hospital da Criança Santo Antônio'],
             ['13 anos de prática', 'Consultora em aleitamento materno', 'Atuação em UTI neonatal']],
            [4, 'Dr. Henrique Brandão', 4, '45678', 'Porto Alegre', 'Centro Histórico', 'Rua dos Andradas, 1234 – 8º andar', 'Centro Ortopédico MedCare', 'presencial', 350, 4.7, 96,
             ['Bradesco Saúde', 'Amil', 'Particular'],
             'Ortopedista especializado em joelho e medicina esportiva. Atende lesões de corredores, dores articulares e reabilitação pós-cirúrgica.',
             ['Medicina – UFRGS', 'Residência em Ortopedia e Traumatologia', 'Fellowship em Cirurgia do Joelho'],
             ['14 anos de experiência', 'Médico de equipes amadoras de futebol', 'Mais de 800 cirurgias artroscópicas']],
            [5, 'Dra. Juliana Prates', 5, '56789', 'Novo Hamburgo', 'Centro', 'Rua Marcílio Dias, 310 – sala 5', 'Clínica da Mulher MedCare', 'ambos', 330, 4.9, 171,
             ['Unimed Exemplo', 'SulAmérica', 'Particular'],
             'Ginecologista e obstetra. Cuida de rotina preventiva, planejamento familiar, pré-natal e climatério, com atendimento presencial ou por vídeo nos retornos.',
             ['Medicina – UFCSPA', 'Residência em Ginecologia e Obstetrícia'],
             ['12 anos de prática', 'Especialização em ginecologia endócrina']],
            [6, 'Dr. Eduardo Faria', 6, '67890', 'Porto Alegre', 'Menino Deus', 'Av. Getúlio Vargas, 702', 'Instituto da Visão MedCare', 'presencial', 300, 4.6, 88,
             ['Unimed Exemplo', 'Amil', 'Particular'],
             'Oftalmologista com ênfase em catarata, glaucoma e correção de miopia. Exames completos de fundo de olho no mesmo dia da consulta.',
             ['Medicina – UFRGS', 'Residência em Oftalmologia – Hospital Banco de Olhos'],
             ['18 anos de atuação', 'Cirurgião de catarata', 'Professor convidado de residência']],
            [7, 'Dra. Camila Rocha', 7, '78901', 'Porto Alegre', 'Auxiliadora', 'Rua Silva Jardim, 215 – sala 41', 'Clínica MedCare Auxiliadora', 'ambos', 400, 4.8, 119,
             ['Bradesco Saúde', 'SulAmérica', 'Particular'],
             'Neurologista focada em enxaqueca, epilepsia e distúrbios do sono. Acompanhamento longitudinal com plano de tratamento claro.',
             ['Medicina – PUCRS', 'Residência em Neurologia', 'Mestrado em Neurociências'],
             ['10 anos de prática', 'Pesquisadora em cefaleias', 'Autora de artigos em revistas científicas']],
            [8, 'Dr. Thiago Vasconcelos', 8, '89012', 'Porto Alegre', 'Bela Vista', 'Rua Marquês do Pombal, 640 – sala 9', 'Espaço Mente Saudável', 'online', 360, 4.9, 203,
             ['Unimed Exemplo', 'Particular'],
             'Psiquiatra que atende adultos com ansiedade, depressão e transtornos do sono. Consultas online, com acolhimento e acompanhamento frequente.',
             ['Medicina – UFRGS', 'Residência em Psiquiatria', 'Formação em Terapia Cognitivo-Comportamental'],
             ['9 anos de atuação', 'Atendimento online desde 2019']],
            [9, 'Dra. Fernanda Klein', 9, '90123', 'Gravataí', 'Centro', 'Av. José Loureiro da Silva, 1500', 'Clínica MedCare Gravataí', 'ambos', 250, 4.7, 156,
             ['Unimed Exemplo', 'Amil', 'SulAmérica', 'Particular'],
             'Clínica geral que coordena o seu cuidado: check-ups, controle de pressão e diabetes, atestados e encaminhamentos para especialistas.',
             ['Medicina – UFCSPA', 'Residência em Clínica Médica'],
             ['8 anos de prática', 'Atuação em unidade básica e hospital']],
            [10, 'Dr. Lucas Teixeira', 10, '01234', 'Canoas', 'Marechal Rondon', 'Av. Guilherme Schell, 2200 – sala 18', 'Clínica MedCare Canoas', 'ambos', 340, 4.8, 109,
             ['Bradesco Saúde', 'Amil', 'Particular'],
             'Endocrinologista especializado em diabetes, tireoide e obesidade, com acompanhamento integrado a nutricionistas.',
             ['Medicina – UFRGS', 'Residência em Endocrinologia e Metabologia'],
             ['12 anos de experiência', 'Coordenador de grupo de diabetes tipo 2']],
            [11, 'Dra. Larissa Moreira', 11, '5-1122', 'Porto Alegre', 'Boa Vista', 'Rua Dr. Florêncio Ygartua, 90 – sala 7', 'Espaço Nutrição MedCare', 'ambos', 220, 4.9, 134,
             ['Unimed Exemplo', 'Particular'],
             'Nutricionista clínica e esportiva. Monta planos alimentares realistas, sem dietas restritivas, com retornos online.',
             ['Nutrição – UFRGS', 'Especialização em Nutrição Clínica'],
             ['9 anos de prática', 'Nutricionista de equipe de remo']],
            [12, 'Dr. Gustavo Ribeiro', 12, '9-3344', 'Porto Alegre', 'Cidade Baixa', 'Rua da República, 410 – sala 2', 'Odonto MedCare Cidade Baixa', 'presencial', 200, 4.7, 91,
             ['Unimed Exemplo', 'Amil', 'Particular'],
             'Cirurgião-dentista com foco em prevenção, limpeza, restaurações e clareamento, em ambiente tranquilo e com agendamento pontual.',
             ['Odontologia – UFRGS', 'Especialização em Dentística'],
             ['14 anos de atuação', 'Atendimento de ansiedade odontológica']],
        ];

        $esp = [];
        foreach (self::especialidades() as $e) {
            $esp[$e['id']] = $e['nome'];
        }

        return array_map(static function ($l) use ($esp) {
            $nomeLimpo = preg_replace('/^(dr|dra)\.\s+/iu', '', $l[1]);
            return [
                'id' => $l[0], 'nome' => $l[1],
                'especialidade_id' => $l[2], 'especialidade' => $esp[$l[2]],
                'crm' => $l[3], 'crm_uf' => 'RS',
                'cidade' => $l[4], 'bairro' => $l[5], 'estado' => 'RS',
                'endereco' => $l[6], 'clinica' => $l[7],
                'modalidade' => $l[8], 'valor_consulta' => (float) $l[9],
                'nota' => $l[10], 'total_avaliacoes' => $l[11],
                'convenios' => $l[12], 'biografia' => $l[13],
                'formacao' => $l[14], 'experiencia' => $l[15],
                'foto' => null, 'ativo' => 1,
                'feminino' => str_starts_with($l[1], 'Dra.'),
                'slug' => strtolower(preg_replace('/\W+/', '-', $nomeLimpo)),
            ];
        }, $m);
    }

    /** Modelos de avaliação; escolhidos de forma estável por médico. */
    public static function avaliacoes(): array
    {
        return [
            ['Ana P.',      5, 'Pontual, atenciosa e explicou tudo com calma. Saí da consulta sabendo exatamente os próximos passos.'],
            ['Ricardo M.',  5, 'Agendar pelo site foi muito fácil e fui atendido no horário marcado. Recomendo.'],
            ['Patrícia S.', 4, 'Ótimo atendimento. O consultório é confortável, só achei o estacionamento pequeno.'],
            ['Marcos T.',   5, 'Muito claro nas explicações e sem pressa. Já indiquei para a família.'],
            ['Luciana F.',  4, 'Consulta completa e objetiva. Os resultados dos exames foram analisados no mesmo dia.'],
        ];
    }
}
