-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           13.0.2-MariaDB - MariaDB Server
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.20.0.7320
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para odontocare
CREATE DATABASE IF NOT EXISTS `odontocare` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `odontocare`;

-- Copiando estrutura para tabela odontocare.consulta
CREATE TABLE IF NOT EXISTS `consulta` (
  `idConsulta` int(11) NOT NULL AUTO_INCREMENT,
  `dataConsulta` date NOT NULL,
  `horarioConsulta` time NOT NULL,
  `statusConsulta` varchar(30) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `idUsuario` int(11) NOT NULL,
  `idPaciente` int(11) NOT NULL,
  `idProcedimento` int(11) NOT NULL,
  PRIMARY KEY (`idConsulta`),
  KEY `idUsuario` (`idUsuario`),
  KEY `idPaciente` (`idPaciente`),
  KEY `idProcedimento` (`idProcedimento`),
  CONSTRAINT `consulta_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  CONSTRAINT `consulta_ibfk_2` FOREIGN KEY (`idPaciente`) REFERENCES `paciente` (`idPaciente`),
  CONSTRAINT `consulta_ibfk_3` FOREIGN KEY (`idProcedimento`) REFERENCES `procedimento` (`idProcedimento`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela odontocare.equipamento
CREATE TABLE IF NOT EXISTS `equipamento` (
  `idEquipamento` int(11) NOT NULL AUTO_INCREMENT,
  `nomeEquipamento` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `statusEquipamento` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idEquipamento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela odontocare.material
CREATE TABLE IF NOT EXISTS `material` (
  `idMaterial` int(11) NOT NULL AUTO_INCREMENT,
  `nomeMaterial` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `quantidade` int(11) NOT NULL,
  `estoqueMinimo` int(11) NOT NULL,
  `unidade` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`idMaterial`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela odontocare.orcamento
CREATE TABLE IF NOT EXISTS `orcamento` (
  `idOrcamento` int(11) NOT NULL AUTO_INCREMENT,
  `valorTotal` decimal(10,2) NOT NULL,
  `dataOrcamento` date NOT NULL,
  `statusOrcamento` varchar(30) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `idPaciente` int(11) NOT NULL,
  PRIMARY KEY (`idOrcamento`),
  KEY `orcamento_ibfk_1` (`idPaciente`),
  CONSTRAINT `orcamento_ibfk_1` FOREIGN KEY (`idPaciente`) REFERENCES `paciente` (`idPaciente`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela odontocare.paciente
CREATE TABLE IF NOT EXISTS `paciente` (
  `idPaciente` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `logradouro` varchar(100) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `bairro` varchar(60) DEFAULT NULL,
  `cidade` varchar(60) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `dataNascimento` date DEFAULT NULL,
  `historicoOdontologico` text DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `exames` text DEFAULT NULL,
  `tratamentosAnteriores` text DEFAULT NULL,
  `prontuarioClinicoDigital` text DEFAULT NULL,
  PRIMARY KEY (`idPaciente`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela odontocare.pagamento
CREATE TABLE IF NOT EXISTS `pagamento` (
  `idPagamento` int(11) NOT NULL AUTO_INCREMENT,
  `valorTotal` decimal(10,2) NOT NULL,
  `formaPagamento` varchar(30) DEFAULT NULL,
  `statusPagamento` varchar(30) DEFAULT NULL,
  `dataPagamento` date DEFAULT NULL,
  `idConsulta` int(11) NOT NULL,
  PRIMARY KEY (`idPagamento`),
  KEY `idConsulta` (`idConsulta`),
  CONSTRAINT `pagamento_ibfk_1` FOREIGN KEY (`idConsulta`) REFERENCES `consulta` (`idConsulta`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela odontocare.procedimento
CREATE TABLE IF NOT EXISTS `procedimento` (
  `idProcedimento` int(11) NOT NULL AUTO_INCREMENT,
  `nomeProcedimento` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valorProcedimento` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`idProcedimento`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela odontocare.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `idUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  `login` varchar(50) NOT NULL,
  `senha` varchar(500) NOT NULL,
  `tipoUsuario` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
