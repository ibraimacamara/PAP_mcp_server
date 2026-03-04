-- ============================================================
-- IBRA PAP - Escola de Gestão
-- Schema + Dados de Teste
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestor_escola CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestor_escola;

-- ------------------------------------------------------------
-- TABELA: users (para fotos de perfil de alunos e professores)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- TABELA: aluno
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno (
    numero_aluno INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    apelido VARCHAR(100) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    contato VARCHAR(30) DEFAULT NULL,
    data_nascimento DATE DEFAULT NULL,
    morada VARCHAR(255) DEFAULT NULL,
    genero VARCHAR(20) DEFAULT NULL,
    user_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- TABELA: encarregado
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS encarregado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    apelido VARCHAR(100) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    contato VARCHAR(30) DEFAULT NULL,
    morada VARCHAR(255) DEFAULT NULL,
    nif VARCHAR(20) DEFAULT NULL
);

-- ------------------------------------------------------------
-- TABELA: aluno_encarregado (relação aluno ↔ encarregado)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno_encarregado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_aluno INT NOT NULL,
    id_encarregado INT NOT NULL,
    laco_familiar VARCHAR(50) DEFAULT 'Pai/Mãe',
    FOREIGN KEY (numero_aluno) REFERENCES aluno(numero_aluno) ON DELETE CASCADE,
    FOREIGN KEY (id_encarregado) REFERENCES encarregado(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- TABELA: professor
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS professor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    contato VARCHAR(30) DEFAULT NULL,
    user_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- TABELA: curso
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS curso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT DEFAULT NULL,
    imagem VARCHAR(255) DEFAULT NULL
);

-- ------------------------------------------------------------
-- TABELA: turma
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS turma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    ciclo_formacao VARCHAR(50) DEFAULT NULL,
    statu VARCHAR(20) DEFAULT 'Ativo',
    FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- TABELA: aluno_turma (inscrição de alunos em turmas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno_turma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_aluno INT NOT NULL,
    turma_id INT NOT NULL,
    UNIQUE KEY uq_aluno_turma (numero_aluno, turma_id),
    FOREIGN KEY (numero_aluno) REFERENCES aluno(numero_aluno) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turma(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- TABELA: aluno_curso (histórico de cursos do aluno)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno_curso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_aluno INT NOT NULL,
    curso_id INT NOT NULL,
    FOREIGN KEY (numero_aluno) REFERENCES aluno(numero_aluno) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE CASCADE
);

-- ============================================================
-- DADOS DE TESTE
-- ============================================================

-- Cursos
INSERT INTO curso (nome, descricao, imagem) VALUES
('Informática e Sistemas', 'Curso técnico de informática, redes e programação.', NULL),
('Gestão Empresarial', 'Curso de gestão, contabilidade e administração de empresas.', NULL),
('Enfermagem', 'Curso técnico de saúde e cuidados de enfermagem.', NULL);

-- Turmas
INSERT INTO turma (curso_id, codigo, ciclo_formacao, statu) VALUES
(1, 'INF-A', '1º Ciclo', 'Ativo'),
(1, 'INF-B', '2º Ciclo', 'Ativo'),
(2, 'GES-A', '1º Ciclo', 'Ativo'),
(3, 'ENF-A', '1º Ciclo', 'Ativo');

-- Professores
INSERT INTO professor (nome, email, contato) VALUES
('Carlos Silva', 'carlos.silva@ibra.edu', '912 111 111'),
('Ana Ferreira', 'ana.ferreira@ibra.edu', '912 222 222'),
('João Mendes', 'joao.mendes@ibra.edu', '912 333 333');

-- Encarregados
INSERT INTO encarregado (nome, apelido, email, contato, morada, nif) VALUES
('António', 'Camara', 'antonio.camara@email.com', '921 000 001', 'Rua das Flores, 10, Bissau', '123456789'),
('Fátima', 'Djalo', 'fatima.djalo@email.com', '921 000 002', 'Av. da Liberdade, 25, Bissau', '987654321'),
('Mamadú', 'Baldé', 'mamadu.balde@email.com', '921 000 003', 'Rua do Mercado, 5, Bafatá', '111222333'),
('Mariama', 'Sow', 'mariama.sow@email.com', '921 000 004', 'Rua Nova, 8, Gabú', '444555666');

-- Alunos
INSERT INTO aluno (nome, apelido, email, contato, data_nascimento, morada, genero) VALUES
('Ibra', 'Camara', 'ibra.camara@aluno.ibra.edu', '931 001 001', '2005-03-15', 'Rua das Flores, 10, Bissau', 'Masculino'),
('Fatoumata', 'Djalo', 'fatoumata.djalo@aluno.ibra.edu', '931 001 002', '2004-07-22', 'Av. da Liberdade, 25, Bissau', 'Feminino'),
('Momodú', 'Baldé', 'modomu.balde@aluno.ibra.edu', '931 001 003', '2005-11-08', 'Rua do Mercado, 5, Bafatá', 'Masculino'),
('Aminata', 'Sow', 'aminata.sow@aluno.ibra.edu', '931 001 004', '2006-01-30', 'Rua Nova, 8, Gabú', 'Feminino'),
('Braima', 'Fati', 'braima.fati@aluno.ibra.edu', '931 001 005', '2004-09-12', 'Bairro Militar, Bissau', 'Masculino');

-- Relação aluno ↔ encarregado
INSERT INTO aluno_encarregado (numero_aluno, id_encarregado, laco_familiar) VALUES
(1, 1, 'Pai'),
(2, 2, 'Mãe'),
(3, 3, 'Pai'),
(4, 4, 'Mãe'),
(5, 1, 'Tio');

-- Inscrição de alunos em turmas
INSERT INTO aluno_turma (numero_aluno, turma_id) VALUES
(1, 1),  -- Ibra → INF-A
(2, 1),  -- Fatoumata → INF-A
(3, 3),  -- Momodú → GES-A
(4, 4),  -- Aminata → ENF-A
(5, 2);  -- Braima → INF-B
