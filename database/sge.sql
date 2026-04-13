-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13-Abr-2026 às 10:52
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
  `nome` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `bi` varchar(15) NOT NULL,
  `contato` varchar(30) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `genero` enum('masculino','feminino') DEFAULT NULL,
  `distrito` varchar(255) DEFAULT NULL,
  `freguesia` varchar(255) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `aluno`
--

INSERT INTO `aluno` (`numero_aluno`, `nome`, `email`, `bi`, `contato`, `data_nascimento`, `morada`, `genero`, `distrito`, `freguesia`, `inserido_em`, `user_id`) VALUES
(3, 'Ibraima Camará', 'danilson@gmail.com', '1234HG5678', '920101018', '2026-03-25', 'Rua frei Carlos 10', 'masculino', 'évora', 'Hortas das Figueiras', '2026-03-25 11:01:58', 14),
(4, 'Helena Pontes', 'helenasousapontes@gmail.com', '1234HG5677', '920156667', '2026-03-25', 'av. dona Leonor fernandez', 'feminino', 'évora', 'Senhora de Saúde', '2026-03-25 17:02:42', 15),
(6, 'lidia frederico có', 'lidiafrederico@gmail.com', '8893H3Z88', '920220220', NULL, 'av. dona leonor fernandes', NULL, NULL, NULL, '2026-03-26 11:59:48', NULL),
(8, 'Tida Vanessa Sané', 'danilsonpedrog@gmail.com', '1234HG56F9', '920101015', '2026-04-06', 'av. dona Leonor fernandez', 'masculino', 'évora', 'Senhora de Saúde', '2026-04-06 11:43:46', 18),
(11, 'Laura Sama', 'laurasama@gmail.com', '7888HH99', NULL, '2006-03-27', NULL, NULL, NULL, NULL, '2026-04-07 11:02:23', NULL),
(12, 'cadia Dabo Sanha', 'cadiadabosanha@gmail.com', '1234HG562', '920121213', '2026-04-11', 'av. dona Leonor fernandez', 'feminino', 'évora', 'Senhora de Saúde', '2026-04-11 08:45:11', 23),
(13, 'Vanessa sané', 'vanessasane@gmail.com', '2113HH776', '910002233', '2008-03-02', 'Rua bernandos de Matos', 'feminino', 'Évora', 'Sr. da Saúde', '2026-04-11 21:03:56', 24),
(17, 'Silvandra da Silva', 'Silvandradasilva@gmail.com', '2113HH779', '910002234', '2008-03-02', 'Rua bernandos de Matos', 'feminino', 'Évora', 'Sr. da Saúde', '2026-04-11 22:39:27', 28),
(18, 'Uminha Balde', 'uminhabalde@gmail.com', '2113HH774', '930333999', '2008-03-02', 'Av. dona leonor fernandez', 'feminino', 'Évora', 'Sr. da Saúde', '2026-04-11 22:46:30', 29);

-- --------------------------------------------------------

--
-- Estrutura da tabela `aluno_curso`
--

CREATE TABLE `aluno_curso` (
  `id` int(11) NOT NULL,
  `numero_aluno` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `aluno_curso`
--

INSERT INTO `aluno_curso` (`id`, `numero_aluno`, `curso_id`, `inserido_em`) VALUES
(3, 3, 1, '2026-03-25 11:01:58'),
(4, 4, 2, '2026-03-25 17:02:42'),
(6, 8, 1, '2026-04-06 11:43:46'),
(7, 12, 2, '2026-04-11 08:45:11'),
(8, 13, 1, '2026-04-11 21:03:56'),
(12, 17, 1, '2026-04-11 22:39:27'),
(13, 18, 2, '2026-04-11 22:46:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `aluno_encarregado`
--

CREATE TABLE `aluno_encarregado` (
  `id` int(11) NOT NULL,
  `numero_aluno` int(11) NOT NULL,
  `encarregado_id` int(11) NOT NULL,
  `laco_familiar` varchar(50) DEFAULT 'Pai/Mãe',
  `tipo` enum('secundario','principal') NOT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `aluno_encarregado`
--

INSERT INTO `aluno_encarregado` (`id`, `numero_aluno`, `encarregado_id`, `laco_familiar`, `tipo`, `inserido_em`) VALUES
(2, 3, 1, 'Mãe', 'secundario', '2026-03-25 11:01:58'),
(3, 4, 1, 'Mãe', 'secundario', '2026-03-25 17:02:42'),
(5, 8, 5, 'Pai', 'principal', '2026-04-06 11:43:46'),
(6, 8, 1, 'mãe', 'secundario', '2026-04-06 11:43:46'),
(7, 12, 8, 'Mãe', 'secundario', '2026-04-11 08:45:11'),
(8, 13, 5, 'Pai', 'secundario', '2026-04-11 21:03:56'),
(12, 17, 1, 'Pai', 'secundario', '2026-04-11 22:39:27'),
(13, 18, 8, 'Mãe', 'secundario', '2026-04-11 22:46:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `aluno_turma`
--

CREATE TABLE `aluno_turma` (
  `id` int(11) NOT NULL,
  `numero_aluno` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `aluno_turma`
--

INSERT INTO `aluno_turma` (`id`, `numero_aluno`, `turma_id`, `inserido_em`) VALUES
(3, 3, 1, '2026-03-25 11:01:58'),
(4, 4, 2, '2026-03-25 17:02:42'),
(6, 8, 1, '2026-04-06 11:43:46'),
(7, 12, 2, '2026-04-11 08:45:11'),
(8, 13, 1, '2026-04-11 21:03:56'),
(12, 17, 2, '2026-04-11 22:39:27'),
(13, 18, 2, '2026-04-11 22:46:30');

-- --------------------------------------------------------

--
-- Estrutura da tabela `curso`
--

CREATE TABLE `curso` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `coordenador` int(11) NOT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `curso`
--

INSERT INTO `curso` (`id`, `nome`, `descricao`, `imagem`, `coordenador`, `inserido_em`) VALUES
(1, 'Programador de Informática', 'Um curso de programação de informática (ou Técnico de Programador de Informática) tem como objetivo capacitar os alunos a desenvolver, instalar e manter aplicações informáticas, sistemas de gestão e bases de dados, preparando-os para o mercado de trabalho em TI', 'curso_1_1774433804.png', 1, '2026-03-23 10:46:08'),
(2, 'Gestão e Instalações de Redes', 'capacita profissionais para instalar, configurar, manter e proteger infraestruturas de redes informáticas. Focado na vertente prática, aborda a administração de servidores (Windows/Linux), segurança de dados, gestão de redes sem fios e suporte técnico, garantindo a operacionalidade de sistemas em contexto organizacional.', 'curso_gest-o-e-instala-es-de-redes_1774457935.jpg', 1, '2026-03-25 16:58:55');

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
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `encarregado`
--

INSERT INTO `encarregado` (`id`, `nome`, `bi`, `email`, `contato`, `morada`, `genero`, `distrito`, `freguesia`, `inserido_em`, `user_id`) VALUES
(1, 'Mussu Mané Djassi', '1234HG5673', 'Muassamane@gmail.com', '920101012', 'Rua frei Carlos 10', 'feminino', 'Évora', 'Senhora de Saúde', '2026-03-23 10:19:45', 1),
(5, 'salamon pedro gomes', '1234HG56F1', 'salamongomes@gmail.com', '920101014', 'av. dona Leonor fernandez', 'masculino', 'Évora', 'Senhora de Saúde', '2026-04-06 11:42:37', 17),
(6, 'Mario Gomes Sá', '1234HG5677', 'mariogomessa@gmail.com', '920101111', 'av.  fernandez oliveira', 'masculino', 'Évora', 'Hortas das Figueiras', '2026-04-09 22:46:25', 19),
(8, 'Satu Mané', '1234H556F6', 'aissatumane1@gmail.com', '920101013', 'Rua frei Carlos 10', 'feminino', 'Évora', 'Senhora de Saúde', '2026-04-11 08:25:59', 21),
(9, 'Bubacar Camará', '1852HH55', 'bubacarcamara@gmail.com', '910211221', 'Rua bernando de Matos', 'masculino', 'Évora', 'Sr. da Saúde', '2026-04-11 08:36:34', 22);

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
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
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `professor`
--

CREATE TABLE `professor` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `bi` varchar(15) NOT NULL,
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
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `professor`
--

INSERT INTO `professor` (`id`, `nome`, `email`, `bi`, `contato`, `data_nascimento`, `morada`, `nacionalidade`, `nif`, `genero`, `distrito`, `freguesia`, `grupo_d`, `tipo_c`, `h_profissional`, `h_academica`, `inserido_em`, `user_id`) VALUES
(1, 'Adão Lopes', 'adaolopes@gmail.com', '1234HG5673', '920101012', '2026-03-23', 'av. dona Leonor fernandez', 'Caboverdiano', '22233355', '', 'Évora', 'Hortas das Figueiras', '550', 'prestação de serviços', 'Personalizado', 'Licenciado', '2026-03-23 10:45:39', 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `turma`
--

CREATE TABLE `turma` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `ciclo_formacao` varchar(50) DEFAULT NULL,
  `diretor` int(11) NOT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `turma`
--

INSERT INTO `turma` (`id`, `curso_id`, `codigo`, `ciclo_formacao`, `diretor`, `inserido_em`) VALUES
(1, 1, 'PI 1ºano', '2026/2029', 1, '2026-03-23 10:46:21'),
(2, 2, 'RG 1ºano', '2026/2029', 1, '2026-03-25 17:00:19');

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
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `primeiro_login` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `username`, `senha`, `categoria`, `foto`, `inserido_em`, `primeiro_login`) VALUES
(1, 'Muassamane@gmail.com', '$2y$10$0nGOdsNGq94An3KAJdTzBOz2RvwyISPqQMhRVuEXPjMX3sif9BhtS', 'encarregado', NULL, '2026-03-23 10:19:45', 0),
(2, 'adaolopes', '$2y$10$O2Db5AatJ2NTDqCnt1vDZOQIj1sg.QQQklytc9pwxoP8bwsaJfW/C', 'admin', 'professor_2.png', '2026-03-23 10:45:39', 2),
(3, 'Muassamane@gmail.com', '$2y$10$QA9ChQT9oy/TAuEbG0JD..JSy9/mW5wR/c1jiP4ohP.pVpEDIaJlq', 'encarregado', NULL, '2026-03-23 10:47:21', 0),
(5, 'ibraimacamara@gmail.com', '$2y$10$H2sofIlK4v79QRlSb5niGubE74iG.CSle1gY14imsJzKBitP8MQ62', 'aluno', 'aluno_1234HG5673.png', '2026-03-23 10:52:25', 0),
(7, 'soniadona@gmail.com', '$2y$10$Sh53PlhjAB4JtJWIjDtlIeyPnotVQ0QM/2x5hh1s/65gwgwqhPRrK', 'funcionario', 'funcionario_7.jpg', '2026-03-23 11:17:03', 5),
(12, 'anapaula@gmail.com', '$2y$10$98ec1cgR5yU6BNVgvLRCaOlqs85Gxkyr3TcGoOMADlp6H9YsYmTLe', 'admin', 'funcionario_12.jpg', '2026-03-24 11:50:06', 1),
(13, 'Mussumane@gmail.com', '$2y$10$aqERzNQ4rcE5zVOrDDgoQ.TjVNWJC6cmrrM23nS6Q3S8Bmm7TafTW', 'admin', 'funcionario_13.png', '2026-03-24 11:54:03', 2),
(14, 'danilson@gmail.com', '$2y$10$nySjBQRDhvIylgXpfMRnF.OsC6TrDZ57myhulAJnOq24FVjBSYpfS', 'aluno', 'aluno_3_1775774404.jpg', '2026-03-25 11:01:58', 0),
(15, 'helenasousapontes@gmail.com', '$2y$10$bMPN5THa40pctF4gdwoRTuhwK04KK/AIir.Shoo3BOFMCOgGUpjmm', 'aluno', 'aluno_1234HG5677.jpg', '2026-03-25 17:02:42', 0),
(16, 'lidiafredericoco@gmail.com', '$2y$10$8sO2O9GW3MTv3YsyFIw0IOzWThurdhTVvi5xmtYrevXpvqFZ/VePS', 'aluno', 'aluno_7_1775774326.jpg', '2026-04-06 11:35:09', 0),
(17, 'salamongomes@gmail.com', '$2y$10$pacqOiZCNW3sEiuibfuurePKaAoEIZclDG89SZfWDvpQpl1uE.Nw.', 'encarregado', NULL, '2026-04-06 11:42:37', 0),
(18, 'danilson@gmail.com', '$2y$10$.yzlHdu2X9X53udUHXAYiuU5eW2uEM5/8U52VFNgO12TnZWrTJI/K', 'aluno', 'aluno_8_1775774292.jpg', '2026-04-06 11:43:46', 0),
(19, 'mariogomessa@gmail.com', '$2y$10$E1A/pV4aFoaYHrkNRHZ.G.Mymh1GmiqqkGmHOVlfs5xj4OzXOpIr2', 'encarregado', NULL, '2026-04-09 22:46:25', 0),
(21, 'aissatumane1@gmail.com', '$2y$10$fCjBYp0QtI6TiaJQQSeWMe0779QpW.Lj2jagGNqnNAO4fWTSOKb0e', 'encarregado', NULL, '2026-04-11 08:25:59', 0),
(22, 'bubacarcamara@gmail.com', '$2b$12$hCZmCi00pVZG1n1NcKPJy.kFhbRcyToG63VHdgftyGTXasK6LghPO', 'encarregado', NULL, '2026-04-11 08:36:34', 0),
(23, 'cadiadabosanha@gmail.com', '$2y$10$PihrV0P8IJLfpFNiAnCZM.lfWlxDqv4mfUZJ461z3Bkn4PkuPTqom', 'aluno', 'aluno_1234HG562.jpg', '2026-04-11 08:45:11', 0),
(24, 'vanessasane@gmail.com', '$2b$12$tq24ftglfhVMNqb.9cMDgu9DvMa101ZFIAbHtikPxU5wAzGA3G/jy', 'aluno', 'aluno_13_1776067508.jpg', '2026-04-11 21:03:56', 0),
(28, 'Silvandradasilva@gmail.com', '$2b$12$fe6N7bSsQ0cqS73qKxKTquuGZAA/yKNDYGIEKtiqBWQuWFnA3/vE6', 'aluno', 'aluno_17_1776067532.jpg', '2026-04-11 22:39:27', 0),
(29, 'uminhabalde@gmail.com', '$2b$12$1gMK20r4Uel390tK0eKbEe3aV0SO/eBSNl9aEogv2S6vwyTTlponO', 'aluno', 'aluno_18_1776067567.jpg', '2026-04-11 22:46:30', 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `aluno`
--
ALTER TABLE `aluno`
  ADD PRIMARY KEY (`numero_aluno`),
  ADD UNIQUE KEY `bi` (`bi`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `aluno_curso`
--
ALTER TABLE `aluno_curso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `numero_aluno` (`numero_aluno`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Índices para tabela `aluno_encarregado`
--
ALTER TABLE `aluno_encarregado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `numero_aluno` (`numero_aluno`),
  ADD KEY `encarregado_id` (`encarregado_id`);

--
-- Índices para tabela `aluno_turma`
--
ALTER TABLE `aluno_turma`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_aluno_turma` (`numero_aluno`,`turma_id`),
  ADD KEY `turma_id` (`turma_id`);

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
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `bi` (`bi`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `professor`
--
ALTER TABLE `professor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bi` (`bi`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `turma`
--
ALTER TABLE `turma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diretor` (`diretor`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aluno`
--
ALTER TABLE `aluno`
  MODIFY `numero_aluno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `aluno_curso`
--
ALTER TABLE `aluno_curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `aluno_encarregado`
--
ALTER TABLE `aluno_encarregado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `aluno_turma`
--
ALTER TABLE `aluno_turma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `curso`
--
ALTER TABLE `curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `encarregado`
--
ALTER TABLE `encarregado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `aluno`
--
ALTER TABLE `aluno`
  ADD CONSTRAINT `aluno_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `aluno_curso`
--
ALTER TABLE `aluno_curso`
  ADD CONSTRAINT `aluno_curso_ibfk_1` FOREIGN KEY (`numero_aluno`) REFERENCES `aluno` (`numero_aluno`) ON DELETE CASCADE,
  ADD CONSTRAINT `aluno_curso_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `curso` (`id`);

--
-- Limitadores para a tabela `aluno_encarregado`
--
ALTER TABLE `aluno_encarregado`
  ADD CONSTRAINT `aluno_encarregado_ibfk_1` FOREIGN KEY (`numero_aluno`) REFERENCES `aluno` (`numero_aluno`) ON DELETE CASCADE,
  ADD CONSTRAINT `aluno_encarregado_ibfk_2` FOREIGN KEY (`encarregado_id`) REFERENCES `encarregado` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `aluno_turma`
--
ALTER TABLE `aluno_turma`
  ADD CONSTRAINT `aluno_turma_ibfk_1` FOREIGN KEY (`numero_aluno`) REFERENCES `aluno` (`numero_aluno`) ON DELETE CASCADE,
  ADD CONSTRAINT `aluno_turma_ibfk_2` FOREIGN KEY (`turma_id`) REFERENCES `turma` (`id`);

--
-- Limitadores para a tabela `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `curso_ibfk_1` FOREIGN KEY (`coordenador`) REFERENCES `professor` (`id`);

--
-- Limitadores para a tabela `encarregado`
--
ALTER TABLE `encarregado`
  ADD CONSTRAINT `encarregado_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `professor`
--
ALTER TABLE `professor`
  ADD CONSTRAINT `professor_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `turma`
--
ALTER TABLE `turma`
  ADD CONSTRAINT `turma_ibfk_1` FOREIGN KEY (`diretor`) REFERENCES `professor` (`id`),
  ADD CONSTRAINT `turma_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `curso` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
