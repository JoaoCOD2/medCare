-- ======================================================================
--  MedCare — banco de dados completo (estrutura + dados de exemplo)
-- ======================================================================
--  Como usar (resumo):
--    1) Ligue o MySQL (XAMPP: botão Start em MySQL).
--    2) Abra http://localhost/phpmyadmin e crie o banco "medcare"
--       (agrupamento utf8mb4_unicode_ci).
--    3) Com o banco selecionado, aba "Importar" > escolha este arquivo > Importar.
--       (ou aba "SQL" > cole o conteúdo > Executar)
--    4) Confira: o banco deve ter 5 tabelas (consultas, especialidades,
--       horarios, medicos, pacientes).
--    5) Ajuste o .env com DATA_DRIVER=pdo e as credenciais DB_*.
--    Passo a passo completo e com explicação: README.md,
--    seção "Como o banco foi criado no MySQL (phpMyAdmin)".
--
--  Seguro para rodar mais de uma vez: NÃO apaga nem duplica nada.
--  Para zerar o banco, veja o bloco "RESET" no final do arquivo.
-- ======================================================================


-- ======================================================================
--  MAPA DO BANCO (como as tabelas se relacionam)
-- ======================================================================
--
--   especialidades 1 ──── N medicos 1 ──── N horarios
--                              │                │
--                              │                │
--                              N                N
--   pacientes 1 ───────── N consultas ─────────┘
--
--   • Uma ESPECIALIDADE tem vários MÉDICOS.
--   • Um MÉDICO tem vários HORÁRIOS (a agenda dele).
--   • Um PACIENTE faz várias CONSULTAS.
--   • Cada CONSULTA liga 1 paciente + 1 médico + 1 horário.
--
-- ----------------------------------------------------------------------
--  O QUE É REGISTRADO EM CADA TABELA, E QUANDO
-- ----------------------------------------------------------------------
--   pacientes       Criado quando a pessoa faz "Criar conta".
--                   Lido quando a pessoa faz login (e-mail + senha).
--
--   especialidades  Cadastrada pelo administrador (ou pelo seed).
--                   Usada nos filtros e nos cartões da página inicial.
--
--   medicos         Cadastrado pelo administrador (ou pelo seed).
--                   Lido na busca, no perfil e no agendamento.
--
--   horarios        Criado para montar a agenda de cada médico.
--                   Fica ocupado = 1 quando uma consulta é agendada
--                   e deve voltar a ocupado = 0 se a consulta for cancelada.
--
--   consultas       Criado quando o paciente confirma o agendamento
--                   (gera o número MC-AAAAMMDD-XXXXXXXX).
--                   O status muda para 'cancelado' ou 'concluido'.
-- ======================================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS medcare
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE medcare;


-- ======================================================================
--  1. ESTRUTURA (TABELAS)
-- ======================================================================

-- ----------------------------------------------------------------------
--  PACIENTES
--  Para que serve : guarda quem usa o site para marcar consultas.
--  O que registra : nome, CPF, e-mail e a senha (como hash).
--  Quando grava   : na tela "Criar conta" (pages/cadastro.php).
--  Quando lê      : no login, no menu "Olá, nome" e em "Minhas consultas".
-- ----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pacientes (
    id         INT          AUTO_INCREMENT PRIMARY KEY               COMMENT 'Identificador do paciente',
    nome       VARCHAR(100) NOT NULL                                 COMMENT 'Nome completo (o menu mostra só o primeiro nome)',
    cpf        VARCHAR(14)  NOT NULL UNIQUE                          COMMENT 'CPF, único por paciente (ex.: 000.000.000-00)',
    email      VARCHAR(100) NOT NULL UNIQUE                          COMMENT 'E-mail usado no login, único por paciente',
    senha      VARCHAR(255) NOT NULL                                 COMMENT 'Hash da senha (password_hash), nunca a senha pura',
    criado_em  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP                COMMENT 'Data e hora do cadastro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Pacientes cadastrados no site (quem faz login e agenda consultas)';


-- ----------------------------------------------------------------------
--  ESPECIALIDADES
--  Para que serve : lista as áreas médicas (Cardiologia, Pediatria...).
--  O que registra : nome da especialidade e o ícone dela.
--  Quando grava   : cadastro feito pelo administrador (ou pelo seed).
--  Quando lê      : filtros de busca e cartões da página inicial.
-- ----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS especialidades (
    id     INT          AUTO_INCREMENT PRIMARY KEY                   COMMENT 'Identificador da especialidade',
    nome   VARCHAR(100) NOT NULL                                     COMMENT 'Nome da especialidade (ex.: Cardiologia)',
    icone  VARCHAR(50)  DEFAULT 'fa-user-md'                         COMMENT 'Classe do ícone no Font Awesome'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Especialidades médicas oferecidas no site';


-- ----------------------------------------------------------------------
--  MEDICOS
--  Para que serve : guarda os profissionais que atendem.
--  O que registra : nome, CRM, especialidade, local e tipo de atendimento.
--  Quando grava   : cadastro feito pelo administrador (ou pelo seed).
--  Quando lê      : busca, perfil do médico e agendamento.
--  Relação        : cada médico pertence a 1 especialidade.
-- ----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medicos (
    id                INT          AUTO_INCREMENT PRIMARY KEY        COMMENT 'Identificador do médico',
    nome              VARCHAR(100) NOT NULL                          COMMENT 'Nome do médico (ex.: Dr. Carlos Eduardo)',
    crm               VARCHAR(20)  NOT NULL UNIQUE                   COMMENT 'Registro no conselho (ex.: CRM/SP 123456), único',
    especialidade_id  INT          NOT NULL                          COMMENT 'Especialidade do médico (liga com especialidades.id)',
    cidade            VARCHAR(100) NOT NULL                          COMMENT 'Cidade do consultório',
    bairro            VARCHAR(100) NOT NULL                          COMMENT 'Bairro do consultório',
    atendimento       ENUM('presencial', 'teleconsulta', 'ambos') DEFAULT 'ambos'
                                                                     COMMENT 'Como o médico atende: presencial, teleconsulta ou ambos',
    foto              VARCHAR(255) DEFAULT 'default.jpg'             COMMENT 'Nome do arquivo da foto',

    KEY idx_medicos_cidade (cidade),
    FOREIGN KEY (especialidade_id) REFERENCES especialidades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Médicos que atendem pelo site';


-- ----------------------------------------------------------------------
--  HORARIOS
--  Para que serve : é a agenda de cada médico (dias e horas de atendimento).
--  O que registra : médico, data, hora e se o horário está ocupado.
--  Quando grava   : o administrador (ou o seed) cria os horários livres.
--  Quando altera  : ocupado = 1 ao agendar; deve voltar a 0 ao cancelar.
--  Regra          : o mesmo médico não pode ter 2 horários iguais (data + hora).
-- ----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS horarios (
    id               INT        AUTO_INCREMENT PRIMARY KEY           COMMENT 'Identificador do horário',
    medico_id        INT        NOT NULL                             COMMENT 'Médico dono deste horário (liga com medicos.id)',
    data_disponivel  DATE       NOT NULL                             COMMENT 'Dia do atendimento',
    hora_disponivel  TIME       NOT NULL                             COMMENT 'Hora do atendimento',
    ocupado          TINYINT(1) DEFAULT 0                            COMMENT '0 = livre, 1 = já agendado',

    UNIQUE KEY uq_horario_medico (medico_id, data_disponivel, hora_disponivel),
    KEY idx_horarios_data (data_disponivel, ocupado),
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Agenda dos médicos: cada linha é um horário que pode ser reservado';


-- ----------------------------------------------------------------------
--  CONSULTAS
--  Para que serve : guarda cada agendamento feito por um paciente.
--  O que registra : número da consulta, paciente, médico, horário,
--                   modalidade (presencial/online) e status.
--  Quando grava   : quando o paciente confirma o agendamento.
--  Quando altera  : status muda para 'cancelado' (paciente cancela)
--                   ou 'concluido' (consulta realizada).
--  Relações       : liga pacientes + medicos + horarios.
-- ----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS consultas (
    id               INT         AUTO_INCREMENT PRIMARY KEY          COMMENT 'Identificador interno da consulta',
    numero_consulta  VARCHAR(50) NOT NULL UNIQUE                     COMMENT 'Número mostrado ao paciente (ex.: MC-20261014-47074554)',
    medico_id        INT         NOT NULL                            COMMENT 'Médico da consulta (liga com medicos.id)',
    horario_id       INT         NOT NULL                            COMMENT 'Horário reservado (liga com horarios.id)',
    paciente_id      INT         NOT NULL                            COMMENT 'Paciente que agendou (liga com pacientes.id)',
    modalidade       VARCHAR(50) NOT NULL                            COMMENT 'Como será atendido: presencial ou online',
    status           ENUM('agendado', 'concluido', 'cancelado') DEFAULT 'agendado'
                                                                     COMMENT 'Situação: agendado, concluido ou cancelado',
    criado_em        TIMESTAMP   DEFAULT CURRENT_TIMESTAMP           COMMENT 'Data e hora em que foi agendada',

    FOREIGN KEY (medico_id)   REFERENCES medicos(id)   ON DELETE CASCADE,
    FOREIGN KEY (horario_id)  REFERENCES horarios(id)  ON DELETE CASCADE,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Consultas agendadas pelos pacientes';


-- ----------------------------------------------------------------------
--  Descrições das tabelas que já existiam no banco
--  (o CREATE TABLE IF NOT EXISTS acima não altera tabelas já criadas;
--   estes comandos só atualizam a descrição, sem mexer em nenhum dado).
-- ----------------------------------------------------------------------
ALTER TABLE pacientes       COMMENT = 'Pacientes cadastrados no site (quem faz login e agenda consultas)';
ALTER TABLE especialidades  COMMENT = 'Especialidades médicas oferecidas no site';
ALTER TABLE medicos         COMMENT = 'Médicos que atendem pelo site';
ALTER TABLE horarios        COMMENT = 'Agenda dos médicos: cada linha é um horário que pode ser reservado';
ALTER TABLE consultas       COMMENT = 'Consultas agendadas pelos pacientes';


-- ======================================================================
--  2. DADOS DE EXEMPLO (SEED)
-- ======================================================================

-- Especialidades
INSERT IGNORE INTO especialidades (id, nome, icone) VALUES
    (1, 'Cardiologia',  'fa-heartbeat'),
    (2, 'Pediatria',    'fa-baby'),
    (3, 'Dermatologia', 'fa-allergies');


-- 12 médicos
INSERT IGNORE INTO medicos (id, nome, crm, especialidade_id, cidade, bairro, atendimento) VALUES
    ( 1, 'Dr. Carlos Eduardo',    'CRM/SP 123456', 1, 'São Paulo',      'Centro',          'ambos'),
    ( 2, 'Dra. Mariana Silva',    'CRM/SP 654321', 2, 'São Paulo',      'Jardins',         'presencial'),
    ( 3, 'Dr. Roberto Almeida',   'CRM/SP 111222', 1, 'São Paulo',      'Pinheiros',       'teleconsulta'),
    ( 4, 'Dra. Juliana Costa',    'CRM/SP 333444', 3, 'São Paulo',      'Vila Mariana',    'ambos'),
    ( 5, 'Dr. Ricardo Santos',    'CRM/RJ 555666', 1, 'Rio de Janeiro', 'Copacabana',      'presencial'),
    ( 6, 'Dra. Amanda Oliveira',  'CRM/RJ 777888', 2, 'Rio de Janeiro', 'Barra da Tijuca', 'ambos'),
    ( 7, 'Dr. Fernando Souza',    'CRM/MG 999000', 3, 'Belo Horizonte', 'Savassi',         'teleconsulta'),
    ( 8, 'Dra. Camila Rodrigues', 'CRM/MG 121314', 2, 'Belo Horizonte', 'Lourdes',         'presencial'),
    ( 9, 'Dr. Lucas Pereira',     'CRM/SP 151617', 1, 'São Paulo',      'Moema',           'ambos'),
    (10, 'Dra. Beatriz Nunes',    'CRM/SP 181920', 3, 'São Paulo',      'Itaim Bibi',      'presencial'),
    (11, 'Dr. Gustavo Ribeiro',   'CRM/PR 212223', 1, 'Curitiba',       'Batel',           'teleconsulta'),
    (12, 'Dra. Letícia Alves',    'CRM/PR 242526', 2, 'Curitiba',       'Centro',          'ambos');


-- Horários: de segunda a sexta, nos próximos 30 dias, para todos os médicos,
-- às 08h, 09h, 10h, 11h, 14h, 15h, 16h e 17h.
-- Só gera se a tabela de horários estiver vazia (não mexe em horários já existentes).
INSERT INTO horarios (medico_id, data_disponivel, hora_disponivel, ocupado)
SELECT m.id,
       DATE_ADD(CURDATE(), INTERVAL dias.n DAY),
       h.hora,
       0
FROM medicos m
CROSS JOIN (
    SELECT a.n + b.n * 10 AS n                          -- números de 0 a 29
    FROM (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
          UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
    CROSS JOIN (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2) b
) dias
CROSS JOIN (
    SELECT '08:00:00' AS hora UNION ALL SELECT '09:00:00' UNION ALL SELECT '10:00:00' UNION ALL SELECT '11:00:00'
    UNION ALL SELECT '14:00:00' UNION ALL SELECT '15:00:00' UNION ALL SELECT '16:00:00' UNION ALL SELECT '17:00:00'
) h
WHERE DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL dias.n DAY)) NOT IN (1, 7)    -- 1 = domingo, 7 = sábado
  AND NOT EXISTS (SELECT 1 FROM horarios);


-- ======================================================================
--  3. CONFERÊNCIAS (opcional): descomente e rode para checar o banco
-- ======================================================================

-- Quantas linhas cada tabela tem:
-- SELECT 'especialidades' AS tabela, COUNT(*) AS total FROM especialidades
-- UNION ALL SELECT 'medicos',   COUNT(*) FROM medicos
-- UNION ALL SELECT 'horarios',  COUNT(*) FROM horarios
-- UNION ALL SELECT 'pacientes', COUNT(*) FROM pacientes
-- UNION ALL SELECT 'consultas', COUNT(*) FROM consultas;

-- Consultas agendadas cujo horário NÃO está marcado como ocupado
-- (se aparecer alguma linha, o sistema não está reservando o horário):
-- SELECT c.numero_consulta, c.horario_id, h.ocupado
-- FROM consultas c JOIN horarios h ON h.id = c.horario_id
-- WHERE c.status = 'agendado' AND h.ocupado = 0;

-- Ver a descrição (COMMENT) de cada coluna de uma tabela:
-- SHOW FULL COLUMNS FROM consultas;


-- ======================================================================
--  4. RESET (opcional e PERIGOSO): apaga TODAS as tabelas e dados
--     Só descomente se quiser recomeçar do zero.
-- ======================================================================
-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS consultas, horarios, medicos, especialidades, pacientes;
-- SET FOREIGN_KEY_CHECKS = 1;
