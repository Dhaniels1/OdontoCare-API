-- Executar apenas em um banco existente; nao altera usuarios ou senhas.
-- Antes da migracao, confira se ha logins duplicados:
-- SELECT login, COUNT(*) AS quantidade FROM usuario GROUP BY login HAVING COUNT(*) > 1;
-- Em caso de duplicidades, a criacao da chave unica falhara com ER_DUP_ENTRY,
-- e os usuarios deverao ser reconciliados manualmente antes de repetir.
USE `odontocare`;
SET @tem_indice = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuario'
      AND INDEX_NAME = 'uq_usuario_login'
      AND NON_UNIQUE = 0
);
SET @sql_login = IF(@tem_indice > 0,
    'SELECT ''Login unico ja configurado.'' AS resultado',
    'ALTER TABLE `usuario` ADD UNIQUE KEY `uq_usuario_login` (`login`)'
);
PREPARE st_login FROM @sql_login;
EXECUTE st_login;
DEALLOCATE PREPARE st_login;
