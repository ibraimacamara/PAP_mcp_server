-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 23-Abr-2026 às 15:27
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
-- Banco de dados: `sgei`
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
  `genero` enum('Masculino','Feminino') DEFAULT NULL,
  `distrito` varchar(255) DEFAULT NULL,
  `freguesia` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `turma_id` int(11) DEFAULT NULL,
  `encarregado_principal_id` int(11) DEFAULT NULL,
  `encarregado_secundario_id` int(11) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `aluno`
--

INSERT INTO `aluno` (`numero_aluno`, `nome`, `email`, `bi`, `contato`, `data_nascimento`, `morada`, `genero`, `distrito`, `freguesia`, `user_id`, `curso_id`, `turma_id`, `encarregado_principal_id`, `encarregado_secundario_id`, `inserido_em`) VALUES
(3, 'fatumata camará', 'fatuamatacamara@gmail.com', '1221HH555', '920201010', '2026-04-22', 'av. dona Leonor fernandez', 'Masculino', 'Évora', 'Dom Duarte', 7, 1, 19, 1, NULL, '2026-04-22 16:01:16'),
(4, 'Ibraima Camará Mané', 'Muassamane@gmail.com', '12345FF79', '930101013', '2026-04-22', 'Rua frei Carlos 10', 'Masculino', 'évora', 'Senhora de Saúde', 8, 3, 20, 1, NULL, '2026-04-22 18:51:10'),
(5, 'Fanta Camará', 'fantacamara@gmail.coom', '12345FF784', '930 400 600', '2026-03-29', 'Rua frei Carlos 10', 'Feminino', 'évora', 'Hortas das Figueiras', 10, 1, 19, NULL, NULL, '2026-04-23 00:56:22'),
(6, 'barru Balde', 'barrubalde@gmail.com', '2112HH222', '930330332', '2007-02-01', 'av. dona leonor fernadez', 'Feminino', 'Évora', 'Sr. da Saude', 13, 1, 19, 20, NULL, '2026-04-23 11:59:54');

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
(1, 'Tec de Video', 'Um curso técnico de vídeo (frequentemente designado como Técnico de Vídeo ou Técnico de Audiovisuais) é uma formação prática e intensiva, com dupla certificação (escolar e profissional, nível 4), concebida para preparar profissionais para as diversas etapas da produção audiovisual. O foco principal é a aquisição de competências técnicas para captar, editar e produzir conteúdos de vídeo', 'curso_tec-de-video_1776463073.jpg', NULL, '2026-04-17 21:57:53'),
(3, 'Programação de informática', 'O curso de Programador de Informática capacita a desenvolver aplicações, websites e software (front-end/back-end), utilizando linguagens como Python, Java, C++ ou HTML. Forma profissionais para analisar sistemas, gerir bases de dados e fazer manutenção de computadores/redes. É uma dupla certificação focada no mercado de trabalho tecnológico', 'curso_3_1776872851.png', 1, '2026-04-22 15:46:45');

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
(1, 'Abubacar Camará', '1122HH33', 'abubacarcamara@gmail.com', '930 456 654', 'Rua Aguinaldo Embalo', 'masculino', 'Évora', 'Sr. da Saúde', '2026-04-17 22:00:12', NULL),
(10, 'candé djaura', '1234HG5678', 'candedjaura@gmail.com', '930300300', 'Rua frei Carlos 10', 'masculino', 'évora', 'Hortas das Figueiras', '2026-04-21 22:26:09', 2),
(20, 'Mussu Mané', '2233HH44', 'mussumane@gmail.com', '930 456 656', 'av. Amilcar Lopes cabral 77', 'feminino', 'Bissau', 'Horta das Figueiras', '2026-04-23 11:19:01', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionario`
--

CREATE TABLE `funcionario` (
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
  `cargo` varchar(255) NOT NULL,
  `tipo_c` enum('contrato sem termo','contrato com termo','prestação de serviços') DEFAULT NULL,
  `h_profissional` varchar(100) DEFAULT NULL,
  `h_academica` varchar(100) DEFAULT NULL,
  `inserido_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `funcionario`
--

INSERT INTO `funcionario` (`id`, `user_id`, `nome`, `bi`, `email`, `contato`, `data_nascimento`, `morada`, `nacionalidade`, `nif`, `genero`, `distrito`, `freguesia`, `cargo`, `tipo_c`, `h_profissional`, `h_academica`, `inserido_em`) VALUES
(1, 1, 'Ibraima Camará', '1234HH78', '', '930 300 300', '2026-04-17', 'av. dona Leonor fernandez', 'Guineense', '323699529', 'masculino', 'Évora', 'Sr. de Saude', 'diretor', 'contrato com termo', 'Personalizado', 'Doutorado', '2026-04-17 17:13:40'),
(3, 12, 'Ana carriço', '1221HH333', 'anacarrico@gmail.com', '920111001', '2026-03-29', 'av. dona leonor fernandez', 'Portuguesa', '900099987', 'feminino', 'Évora', 'Sr. de Saude', 'directora pedagógica', 'contrato com termo', 'Personalizado', 'Mestrado', '2026-04-23 11:15:05');

-- --------------------------------------------------------

--
-- Estrutura da tabela `modulo`
--

CREATE TABLE `modulo` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `nome_modulo` varchar(150) NOT NULL,
  `codigo_modulo` varchar(50) NOT NULL,
  `ordem` int(11) DEFAULT 0,
  `carga_horaria` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `modulo`
--

INSERT INTO `modulo` (`id`, `id_curso`, `nome_modulo`, `codigo_modulo`, `ordem`, `carga_horaria`) VALUES
(1, 3, 'Programação orientado a objecto', '0080', 1, 70),
(3, 3, 'php', '0090', 1, 70);

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
(1, 6, 'Adão Lopes', '9876HH321', 'adaolopes@gmail.com', '910 890 098', '2026-03-29', 'av. dona leonor fernandez', 'Portugues', '900099987', '', 'Évora', 'Hortas das Figueiras', '550', 'contrato com termo', 'Profissinalizado', 'Mestrado', '2026-04-22 15:46:00');

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
(19, 1, 0, 'VD 3ºano', '2023/2027', '2026-04-17 21:58:44'),
(20, 3, 1, 'PI 1ºano', '2026/2029', '2026-04-22 15:47:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `turma_modulo_professor`
--

CREATE TABLE `turma_modulo_professor` (
  `id_relacao` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `id_professor` int(11) NOT NULL,
  `estado` enum('ativo','concluido','pendente') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'ibraimacamara', '$2y$10$m4LMg.Cmjr.kVljnfdgjpOZxPiQfZZGVn15wVepKahAIrew9/sbVO', 'admin', 'funcionario_1.png', 2, '2026-04-17 17:13:40'),
(2, 'candedjaura@gmail.com', '$2y$10$2/0ohEK7EAqADTirS5MW6.Yy/hozuOtflML22MyQveY3WrU1I19O6', 'encarregado', NULL, 0, '2026-04-21 22:26:09'),
(3, 'anapaula', '$2y$10$nxL94dl8jFe8G3Ha/btV7eCECS.4EdCybvJABQlX9qDf0RzY4dELy', 'funcionario', 'funcionario_3.jpg', 7, '2026-04-22 00:31:17'),
(6, 'adaolopes@gmail.com', '$2y$10$/J/TPytdGeXW1AiQ2ng59uXO9SKpbF7I/G9FpoNLBb6wGVd/eHyA2', 'professor', 'professor_6.png', 0, '2026-04-22 15:46:00'),
(7, 'fatuamatacamara@gmail.com', '$2y$10$jJu3Nel/SdE3i76mFpqbwO3.2O9lt5fB0.K5l1V6fP8XaTptTQMTi', 'aluno', 'aluno_1221HH555.jpg', 0, '2026-04-22 16:01:16'),
(8, 'Muassamane@gmail.com', '$2y$10$ieRgTU82svIboX/N2S1gKeXrV9/1Vwn40e9ArxRaRu2dj.LlTBd4K', 'aluno', 'aluno_4_1776883906.jpg', 0, '2026-04-22 18:51:10'),
(9, 'claudioramos@gmail.com', '$2y$10$.9DvpbZOOWssJw1m/VILkuisFart6h3.tfSgCarGhJlXuVAYBozIy', 'funcionario', NULL, 2, '2026-04-22 22:24:29'),
(10, 'fantacamara@gmail.coom', '$2y$10$XqIWbtSwb8PECMilBPP.SekWBT3dmRpful6BEsk7mTDuaTE/N8eZK', 'aluno', 'aluno_12345FF784.jpg', 0, '2026-04-23 00:56:22'),
(12, 'anacarrico', '$2y$10$0Fy5y2pgiizw9Em448FFd.7WROQvhEytOVZv1cESHgmAdPfTG0hV2', 'funcionario', 'funcionario_6b2bc1da824c5ff9b85a8a7d49d05f74.png', 3, '2026-04-23 11:15:05'),
(13, 'barrubalde@gmail.com', '2299b822583b8d31bb5ebc7502fee3aca7f774c82faca353899fd6223b94966a', 'aluno', NULL, 0, '2026-04-23 11:59:54');

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
  ADD KEY `fk_aluno_encarregado_secundario` (`encarregado_secundario_id`);

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
  ADD UNIQUE KEY `bi` (`bi`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_modulo` (`codigo_modulo`),
  ADD KEY `fk_modulo_curso` (`id_curso`);

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
-- Índices para tabela `turma_modulo_professor`
--
ALTER TABLE `turma_modulo_professor`
  ADD PRIMARY KEY (`id_relacao`),
  ADD UNIQUE KEY `uq_tmp_turma_modulo` (`id_turma`,`id_modulo`),
  ADD KEY `fk_tmp_modulo` (`id_modulo`),
  ADD KEY `fk_tmp_professor` (`id_professor`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `encarregado`
--
ALTER TABLE `encarregado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `modulo`
--
ALTER TABLE `modulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `professor`
--
ALTER TABLE `professor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `turma`
--
ALTER TABLE `turma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `turma_modulo_professor`
--
ALTER TABLE `turma_modulo_professor`
  MODIFY `id_relacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `aluno`
--
ALTER TABLE `aluno`
  ADD CONSTRAINT `fk_aluno_curso` FOREIGN KEY (`curso_id`) REFERENCES `curso` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aluno_encarregado_principal` FOREIGN KEY (`encarregado_principal_id`) REFERENCES `encarregado` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aluno_encarregado_secundario` FOREIGN KEY (`encarregado_secundario_id`) REFERENCES `encarregado` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_aluno_turma` FOREIGN KEY (`turma_id`) REFERENCES `turma` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
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
-- Limitadores para a tabela `modulo`
--
ALTER TABLE `modulo`
  ADD CONSTRAINT `fk_modulo_curso` FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id`);

--
-- Limitadores para a tabela `professor`
--
ALTER TABLE `professor`
  ADD CONSTRAINT `fk_professor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `turma`
--
ALTER TABLE `turma`
  ADD CONSTRAINT `fk_turma_curso` FOREIGN KEY (`curso_id`) REFERENCES `curso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `turma_modulo_professor`
--
ALTER TABLE `turma_modulo_professor`
  ADD CONSTRAINT `fk_tmp_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id`),
  ADD CONSTRAINT `fk_tmp_professor` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id`),
  ADD CONSTRAINT `fk_tmp_turma` FOREIGN KEY (`id_turma`) REFERENCES `turma` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
