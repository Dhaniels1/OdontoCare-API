-- Execute APÓS criar as tabelas do database/odontocare.sql.
USE odontocare;
CREATE TABLE IF NOT EXISTS movimentacao_estoque (
    idMovimentacao INT NOT NULL AUTO_INCREMENT,
    idMaterial INT NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    quantidade INT NOT NULL,
    dataUtc DATETIME(6) NOT NULL,
    idUsuario INT NOT NULL,
    PRIMARY KEY (idMovimentacao),
    INDEX idx_mov_estoque_material (idMaterial),
    INDEX idx_mov_estoque_usuario (idUsuario),
    CONSTRAINT fk_mov_estoque_material FOREIGN KEY (idMaterial) REFERENCES material(idMaterial),
    CONSTRAINT fk_mov_estoque_usuario FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
