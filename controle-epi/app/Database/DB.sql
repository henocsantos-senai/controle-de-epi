CREATE DATABASE IF NOT EXISTS sistema_epi 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sistema_epi;

-- 1. Tabela de Equipamentos
CREATE TABLE IF NOT EXISTS equipamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ca VARCHAR(30) NOT NULL COMMENT 'Certificado de Aprovação',
    descricao VARCHAR(255) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabela de Colaboradores
CREATE TABLE IF NOT EXISTS colaboradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    telefone VARCHAR(20) DEFAULT NULL,
    cargo VARCHAR(60) NOT NULL,
    setor VARCHAR(60) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabela de Usuários (com perfil já incluído)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    perfil ENUM('Administrador', 'Operador') NOT NULL DEFAULT 'Operador',
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Tabela de Recuperação de Senha
CREATE TABLE IF NOT EXISTS recuperacao_senha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL COMMENT 'Hash SHA-256 do token',
    expira_em DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_hash (token_hash),
    CONSTRAINT fk_rec_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabela de Controle de Empréstimos de EPI
CREATE TABLE IF NOT EXISTS emprestimos_epi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipamento_id INT NOT NULL,
    colaborador_id INT NOT NULL,
    data_emprestimo DATE NOT NULL,
    data_prevista_devolucao DATE DEFAULT NULL,
    data_devolucao DATE DEFAULT NULL,
    status ENUM('Emprestado', 'Fornecido', 'Devolvido', 'Danificado', 'Perdido') NOT NULL DEFAULT 'Emprestado',
    condicoes_emprestimo TEXT DEFAULT NULL,
    observacao_devolucao TEXT DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_epi_equipamento FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_epi_colaborador FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 6. Inserção do Administrador Padrão
-- E-mail: admin@email.com | Senha: 123456
INSERT INTO usuarios (nome, email, perfil, senha)
SELECT 'Administrador', 'admin@email.com', 'Administrador', '$2y$10$tH79l/t4x8/rZ2zLhMhYEu6P4y9r.v8VpW0aE6j7vN7Yk6qFvT7uO'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@email.com');