-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14-Abr-2026 às 11:28
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sge`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `aluno`
--

CREATE TABLE `aluno` (
  `numero_aluno` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `curso_id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `encarregado_principal_id` int(11) DEFAULT NULL,
  `encarregado_secundario_id` int(11) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `bi` varchar(15) NOT NULL,
  `contato` varchar(30) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `genero` enum('masculino','feminino') DEFAULT NULL,
  `distrito` varchar(255) DEFAULT NULL,
  `freguesia` varchar(255) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `aluno`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `curso`
--

CREATE TABLE `curso` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `coordenador` int(11) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `curso`
--

INSERT INTO `curso` (`id`, `nome`, `descricao`, `imagem`, `coordenador`, `inserido_em`) VALUES
(1, 'Programador de Informática', 'eeeee', 'curso_programador-de-inform-tica_1776130968.png', 1, '2026-04-14 01:42:48');

-- --------------------------------------------------------

--
-- Estrutura da tabela `encarregado`
--

CREATE TABLE `encarregado` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `bi` varchar(15) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contato` varchar(30) DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `genero` enum('masculino','feminino') DEFAULT NULL,
  `distrito` varchar(20) DEFAULT NULL,
  `freguesia` varchar(20) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `encarregado`
--

INSERT INTO `encarregado` (`id`, `nome`, `bi`, `email`, `contato`, `morada`, `genero`, `distrito`, `freguesia`, `inserido_em`, `user_id`) VALUES
(1, 'Adão lopes', '1234HG5673', 'Muassamane@gmail.com', '920101111', 'Rua frei Carlos 10', 'masculino', 'évora', 'Hortas das Figueiras', '2026-04-14 01:24:22', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `bi` varchar(15) NOT NULL,
  `contato` varchar(30) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `nacionalidade` varchar(255) DEFAULT NULL,
  `nif` varchar(255) DEFAULT NULL,
  `genero` enum('masculino','feminino') DEFAULT NULL,
  `distrito` varchar(255) DEFAULT NULL,
  `freguesia` varchar(255) DEFAULT NULL,
  `cargo` varchar(255) NOT NULL,
  `tipo_c` enum('contrato sem termo','contrato com termo','prestação de serviços') DEFAULT NULL,
  `h_profissional` varchar(100) DEFAULT NULL,
  `h_academica` varchar(100) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `professor`
--

CREATE TABLE `professor` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `bi` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contato` varchar(30) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `nacionalidade` varchar(255) DEFAULT NULL,
  `nif` varchar(255) DEFAULT NULL,
  `genero` enum('masculino','feminino') DEFAULT NULL,
  `distrito` varchar(255) DEFAULT NULL,
  `freguesia` varchar(255) DEFAULT NULL,
  `grupo_d` varchar(10) DEFAULT NULL,
  `tipo_c` enum('contrato sem termo','contrato com termo','prestação de serviços') DEFAULT NULL,
  `h_profissional` varchar(100) DEFAULT NULL,
  `h_academica` varchar(100) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `professor`
--

INSERT INTO `professor` (`id`, `user_id`, `nome`, `bi`, `email`, `contato`, `data_nascimento`, `morada`, `nacionalidade`, `nif`, `genero`, `distrito`, `freguesia`, `grupo_d`, `tipo_c`, `h_profissional`, `h_academica`, `inserido_em`) VALUES
(1, 3, 'Adão lopes', '1234HG5673', 'adaolope@gmail.com', '920101012', '2026-04-14', 'Rua frei Carlos 10', 'Caboverdiano', '22233355', 'masculino', 'Évora', 'Senhora de Saúde', '550', 'prestação de serviços', 'Personalizado', 'Licenciado', '2026-04-14 01:34:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `turma`
--

CREATE TABLE `turma` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `diretor` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `ciclo_formacao` varchar(50) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `turma`
--

INSERT INTO `turma` (`id`, `curso_id`, `diretor`, `codigo`, `ciclo_formacao`, `inserido_em`) VALUES
(1, 1, 1, 'PI 1ºano', '2026/2029', '2026-04-14 01:42:58');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `categoria` enum('admin','funcionario','professor','encarregado','aluno') DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `primeiro_login` tinyint(1) NOT NULL DEFAULT 0,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `username`, `senha`, `categoria`, `foto`, `primeiro_login`, `inserido_em`) VALUES
(1, 'Muassamane@gmail.com', '$2y$10$zWj4QwtV6yhA0mS6sD1CDeiE0qzaUdJqhi7341pO1HYOPDw6GKvky', 'admin', NULL, 0, '2026-04-14 01:24:22'),
(2, 'adaolopes@gmail.com', '$2y$10$p3snT3A77z1JFnPqW6yPC.0m1uP6o4aKYEr.bUTOtYr00gx5hvYHq', 'admin', NULL, 4, '2026-04-14 01:25:54'),
(3, 'adaolopes', '$2y$10$h9A/FICEjD0ZhTShlXjtROc4Os6jzW4zfDXr2s6KSp31nPxRWoUVu', 'admin', 'professor_1234HG5673.png', 3, '2026-04-14 01:34:57'),
(5, 'ibraimacamara@gmail.com', '$2y$10$Jg1XX/9aNHDTl3E4Hfe9AOB2bloCcPi1kfLnl8WhZIQE.r/TZBdSS', 'aluno', 'aluno_1_1776156786.jpg', 0, '2026-04-14 01:50:37'),
(6, 'danilsonpedrogomes@gmail.com', '$2y$10$teScJmJzyZzhpUGaTvsjCOK9QGBDbROt.NJ3W0DOAvbfWmzOsL7mu', 'aluno', 'aluno_4_1776156804.jpg', 0, '2026-04-14 08:50:30'),
(7, 'caramoture@gmail.com', '$2y$10$0u7rdNkdUoSHGMMwcFB.LOu975mCErUQnQMkw0tayRiHmIiOXmO..', 'aluno', 'aluno_5_1776156815.jpg', 0, '2026-04-14 08:51:15'),
(8, 'ensacamara@gmail.com', '$2y$10$R4Fnar94XxufrvxGr.xKquuswLox866n4kpTAyJg/.4UWI3sZCh9W', 'aluno', 'aluno_6_1776156770.png', 0, '2026-04-14 08:51:59');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `aluno`
--
ALTER TABLE `aluno`
  ADD PRIMARY KEY (`numero_aluno`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `bi` (`bi`),
  ADD KEY `fk_aluno_user` (`user_id`),
  ADD KEY `fk_aluno_curso` (`curso_id`),
  ADD KEY `fk_aluno_turma` (`turma_id`),
  ADD KEY `fk_aluno_encarregado_principal` (`encarregado_principal_id`),
  ADD KEY `fk_aluno_encarregado_sencudario` (`encarregado_secundario_id`);

--
-- Índices para tabela `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coordenador` (`coordenador`);

--
-- Índices para tabela `encarregado`
--
ALTER TABLE `encarregado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bi` (`bi`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `bi` (`bi`);

--
-- Índices para tabela `professor`
--
ALTER TABLE `professor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `bi` (`bi`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `turma`
--
ALTER TABLE `turma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_turma_curso` (`curso_id`),
  ADD KEY `fk_turma_diretor` (`diretor`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aluno`
--
ALTER TABLE `aluno`
  MODIFY `numero_aluno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `curso`
--
ALTER TABLE `curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `encarregado`
--
ALTER TABLE `encarregado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `professor`
--
ALTER TABLE `professor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `turma`
--
ALTER TABLE `turma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `aluno`
--
ALTER TABLE `aluno`
  ADD CONSTRAINT `fk_aluno_curso` FOREIGN KEY (`curso_id`) REFERENCES `curso` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aluno_encarregado_principal` FOREIGN KEY (`encarregado_principal_id`) REFERENCES `encarregado` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aluno_encarregado_sencudario` FOREIGN KEY (`encarregado_secundario_id`) REFERENCES `encarregado` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aluno_turma` FOREIGN KEY (`turma_id`) REFERENCES `turma` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aluno_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `fk_curso_professor` FOREIGN KEY (`coordenador`) REFERENCES `professor` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `encarregado`
--
ALTER TABLE `encarregado`
  ADD CONSTRAINT `fk_encarregado_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `fk_funcionario_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `professor`
--
ALTER TABLE `professor`
  ADD CONSTRAINT `fk_professor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `turma`
--
ALTER TABLE `turma`
  ADD CONSTRAINT `fk_turma_curso` FOREIGN KEY (`curso_id`) REFERENCES `curso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turma_diretor` FOREIGN KEY (`diretor`) REFERENCES `professor` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
