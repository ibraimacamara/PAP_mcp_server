-- ============================================================
-- IBRA PAP - Escola de Gestão
-- Schema + Dados de Teste
-- ============================================================

CREATE DATABASE IF NOT EXISTS SGE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE SGE;

-- ------------------------------------------------------------
-- TABELA: users (para fotos de perfil de alunos e professores)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    categoria ENUM('admin','funcionario','professor','encarregado','aluno'),
    foto VARCHAR(255) DEFAULT NULL,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- TABELA: aluno
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno (
    numero_aluno INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    bi VARCHAR(15) NOT NULL UNIQUE,
    contato VARCHAR(30) DEFAULT NULL,
    data_nascimento DATE DEFAULT NULL,
    morada VARCHAR(255) DEFAULT NULL,
    genero ENUM('masculino', 'feminino') DEFAULT NULL,
    distrito VARCHAR(255) DEFAULT NULL,
    freguesia VARCHAR(255) DEFAULT NULL,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- TABELA: encarregado
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS encarregado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    bi VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(150) DEFAULT NULL,
    contato VARCHAR(30) DEFAULT NULL,
    morada VARCHAR(255) DEFAULT NULL,
    genero ENUM('masculino', 'feminino') DEFAULT NULL,
    distrito VARCHAR(20) DEFAULT NULL,
    freguesia VARCHAR(20) DEFAULT NULL,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id int not null,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ------------------------------------------------------------
-- TABELA: aluno_encarregado (relação aluno ↔ encarregado)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno_encarregado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_aluno INT NOT NULL,
    encarregado_id INT NOT NULL,
    laco_familiar VARCHAR(50) DEFAULT 'Pai/Mãe',
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (numero_aluno) REFERENCES aluno (numero_aluno) ON DELETE CASCADE,
    FOREIGN KEY (encarregado_id) REFERENCES encarregado (id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- TABELA: professor
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS professor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    bi VARCHAR(15) NOT NULL UNIQUE,
    contato VARCHAR(30) DEFAULT NULL,
    data_nascimento DATE DEFAULT NULL,
    morada VARCHAR(255) DEFAULT NULL,
    nacionalidade VARCHAR(255) DEFAULT NULL,
    nif VARCHAR(255) DEFAULT NULL,
    genero ENUM('masculino', 'feminino') DEFAULT NULL,
    distrito VARCHAR(255) DEFAULT NULL,
    freguesia VARCHAR(255) DEFAULT NULL,
    grupo_d VARCHAR(10) DEFAULT NULL,
    tipo_c ENUM(
        'contrato sem termo',
        'contrato com termo',
        'prestação de serviços'
    ) DEFAULT NULL,
    h_profissional VARCHAR(100) DEFAULT NULL,
    h_academica VARCHAR(100) DEFAULT NULL,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id)
);

-- ------------------------------------------------------------
-- TABELA: professor
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS funcionario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    bi VARCHAR(15) NOT NULL UNIQUE,
    contato VARCHAR(30) DEFAULT NULL,
    data_nascimento DATE DEFAULT NULL,
    morada VARCHAR(255) DEFAULT NULL,
    nacionalidade VARCHAR(255) DEFAULT NULL,
    nif VARCHAR(255) DEFAULT NULL,
    genero ENUM('masculino', 'feminino') DEFAULT NULL,
    distrito VARCHAR(255) DEFAULT NULL,
    freguesia VARCHAR(255) DEFAULT NULL,
    cargo VARCHAR(255) NOT NULL,
    tipo_c ENUM(
        'contrato sem termo',
        'contrato com termo',
        'prestação de serviços'
    ) DEFAULT NULL,
    h_profissional VARCHAR(100) DEFAULT NULL,
    h_academica VARCHAR(100) DEFAULT NULL,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id)
);

-- ------------------------------------------------------------
-- TABELA: curso
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS curso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT DEFAULT NULL,
    imagem VARCHAR(255) DEFAULT NULL,
    coordenador int(11) NOT NULL,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coordenador) REFERENCES professor(id) 
);

-- ------------------------------------------------------------
-- TABELA: turma
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS turma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    ciclo_formacao VARCHAR(50) DEFAULT NULL,
    diretor int not null,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (diretor) REFERENCES professor(id),
    FOREIGN KEY (curso_id) REFERENCES curso (id) 
);

-- ------------------------------------------------------------
-- TABELA: aluno_turma (inscrição de alunos em turmas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno_turma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_aluno INT NOT NULL,
    turma_id INT NOT NULL,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_aluno_turma (numero_aluno, turma_id),
    FOREIGN KEY (numero_aluno) REFERENCES aluno (numero_aluno) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turma (id) 
);

-- ------------------------------------------------------------
-- TABELA: aluno_curso (histórico de cursos do aluno)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aluno_curso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_aluno INT NOT NULL,
    curso_id INT NOT NULL,
    inserido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (numero_aluno) REFERENCES aluno (numero_aluno) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES curso (id) 
);

-- ============================================================
-- DADOS DE TESTE
-- ============================================================

-- Cursos
-- INSERT INTO
--     curso (nome, descricao, imagem)
-- VALUES (
--         'Informática e Sistemas',
--         'Curso técnico de informática, redes e programação.',
--         NULL
--     ),
--     (
--         'Gestão Empresarial',
--         'Curso de gestão, contabilidade e administração de empresas.',
--         NULL
--     ),
--     (
--         'Enfermagem',
--         'Curso técnico de saúde e cuidados de enfermagem.',
--         NULL
--     );

-- -- Turmas
-- INSERT INTO
--     turma (
--         curso_id,
--         codigo,
--         ciclo_formacao,
--         statu
--     )
-- VALUES (
--         1,
--         'INF-A',
--         '1º Ciclo',
--     ),
--     (
--         1,
--         'INF-B',
--         '2º Ciclo',

--     ),
--     (
--         2,
--         'GES-A',
--         '1º Ciclo',

--     ),
--     (
--         3,
--         'ENF-A',
--         '1º Ciclo',

--     );

-- -- Professores
-- INSERT INTO
--     professor (nome, email, contato)
-- VALUES (
--         'Carlos Silva',
--         'carlos.silva@ibra.edu',
--         '912 111 111'
--     ),
--     (
--         'Ana Ferreira',
--         'ana.ferreira@ibra.edu',
--         '912 222 222'
--     ),
--     (
--         'João Mendes',
--         'joao.mendes@ibra.edu',
--         '912 333 333'
--     );

-- -- Encarregados
-- INSERT INTO
--     encarregado (
--         nome,
--         apelido,
--         email,
--         contato,
--         morada,
--         nif
--     )
-- VALUES (
--         'António',
--         'Camara',
--         'antonio.camara@email.com',
--         '921 000 001',
--         'Rua das Flores, 10, Bissau',
--         '123456789'
--     ),
--     (
--         'Fátima',
--         'Djalo',
--         'fatima.djalo@email.com',
--         '921 000 002',
--         'Av. da Liberdade, 25, Bissau',
--         '987654321'
--     ),
--     (
--         'Mamadú',
--         'Baldé',
--         'mamadu.balde@email.com',
--         '921 000 003',
--         'Rua do Mercado, 5, Bafatá',
--         '111222333'
--     ),
--     (
--         'Mariama',
--         'Sow',
--         'mariama.sow@email.com',
--         '921 000 004',
--         'Rua Nova, 8, Gabú',
--         '444555666'
--     );

-- -- Alunos
-- INSERT INTO
--     aluno (
--         nome,
--         apelido,
--         email,
--         contato,
--         data_nascimento,
--         morada,
--         genero
--     )
-- VALUES (
--         'Ibra',
--         'Camara',
--         'ibra.camara@aluno.ibra.edu',
--         '931 001 001',
--         '2005-03-15',
--         'Rua das Flores, 10, Bissau',
--         'Masculino'
--     ),
--     (
--         'Fatoumata',
--         'Djalo',
--         'fatoumata.djalo@aluno.ibra.edu',
--         '931 001 002',
--         '2004-07-22',
--         'Av. da Liberdade, 25, Bissau',
--         'Feminino'
--     ),
--     (
--         'Momodú',
--         'Baldé',
--         'modomu.balde@aluno.ibra.edu',
--         '931 001 003',
--         '2005-11-08',
--         'Rua do Mercado, 5, Bafatá',
--         'Masculino'
--     ),
--     (
--         'Aminata',
--         'Sow',
--         'aminata.sow@aluno.ibra.edu',
--         '931 001 004',
--         '2006-01-30',
--         'Rua Nova, 8, Gabú',
--         'Feminino'
--     ),
--     (
--         'Braima',
--         'Fati',
--         'braima.fati@aluno.ibra.edu',
--         '931 001 005',
--         '2004-09-12',
--         'Bairro Militar, Bissau',
--         'Masculino'
--     );

-- -- Relação aluno ↔ encarregado
-- INSERT INTO
--     aluno_encarregado (
--         numero_aluno,
--         id_encarregado,
--         laco_familiar
--     )
-- VALUES (1, 1, 'Pai'),
--     (2, 2, 'Mãe'),
--     (3, 3, 'Pai'),
--     (4, 4, 'Mãe'),
--     (5, 1, 'Tio');

-- -- Inscrição de alunos em turmas
-- INSERT INTO
--     aluno_turma (numero_aluno, turma_id)
-- VALUES (1, 1), -- Ibra → INF-A
--     (2, 1), -- Fatoumata → INF-A
--     (3, 3), -- Momodú → GES-A
--     (4, 4), -- Aminata → ENF-A
--     (5, 2);
-- -- Braima → INF-B