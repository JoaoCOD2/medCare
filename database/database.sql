-- =====================================================================
-- MedCare — estrutura do banco de dados (tabelas)
-- Seguro para rodar mais de uma vez: NÃO apaga nada que já existe.
-- Depois de rodar este arquivo, rode o database/seed.sql (dados de exemplo).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS medcare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medcare;

-- 1. Pacientes (cadastro e login)
CREATE TABLE IF NOT EXISTS pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,              -- guarda o hash (password_hash), nunca a senha pura
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Especialidades
CREATE TABLE IF NOT EXISTS especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    icone VARCHAR(50) DEFAULT 'fa-user-md'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Médicos
CREATE TABLE IF NOT EXISTS medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    crm VARCHAR(20) NOT NULL UNIQUE,
    especialidade_id INT NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    bairro VARCHAR(100) NOT NULL,
    atendimento ENUM('presencial', 'teleconsulta', 'ambos') DEFAULT 'ambos',
    foto VARCHAR(255) DEFAULT 'default.jpg',
    FOREIGN KEY (especialidade_id) REFERENCES especialidades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Horários disponíveis de cada médico
CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medico_id INT NOT NULL,
    data_disponivel DATE NOT NULL,
    hora_disponivel TIME NOT NULL,
    ocupado TINYINT(1) DEFAULT 0,
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Consultas agendadas
CREATE TABLE IF NOT EXISTS consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_consulta VARCHAR(50) NOT NULL UNIQUE,
    medico_id INT NOT NULL,
    horario_id INT NOT NULL,
    paciente_id INT NOT NULL,
    modalidade VARCHAR(50) NOT NULL,
    status ENUM('agendado', 'concluido', 'cancelado') DEFAULT 'agendado',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    FOREIGN KEY (horario_id) REFERENCES horarios(id) ON DELETE CASCADE,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- RESET TOTAL (opcional, PERIGOSO): apaga todas as tabelas e os dados.
-- Só descomente se quiser zerar o banco do zero.
-- ---------------------------------------------------------------------
-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS consultas, horarios, medicos, especialidades, pacientes;
-- SET FOREIGN_KEY_CHECKS = 1;