<?php
// Execute SOMENTE via terminal, preferencialmente após backup do banco.
// Altera senhas legadas em texto puro para PBKDF2-HMAC-SHA256; NUNCA mostra senhas.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$host = getenv('ODONTOCARE_DB_HOST') ?: '127.0.0.1';
$name = getenv('ODONTOCARE_DB_NAME') ?: 'odontocare';
$user = getenv('ODONTOCARE_DB_USER');
$pass = getenv('ODONTOCARE_DB_PASS');
if ($user === false || $pass === false) {
    fwrite(STDERR, "Configure ODONTOCARE_DB_USER e ODONTOCARE_DB_PASS no ambiente.\n"); exit(1);
}
try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->beginTransaction();
    $usuarios = $pdo->query('SELECT idUsuario, senha FROM usuario FOR UPDATE')->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare('UPDATE usuario SET senha=? WHERE idUsuario=?');
    $contador = 0;
    foreach ($usuarios as $usuario) {
        $senha = $usuario['senha'];
        if (str_starts_with($senha, 'pbkdf2-sha256$')) continue;
        // Somente usar para migrar base legada autenticada; resetar senhas após migração é recomendado.
        $sal = random_bytes(16);
        $hash = hash_pbkdf2('sha256', $senha, $sal, 210000, 32, true);
        $novo = 'pbkdf2-sha256$210000$' . base64_encode($sal) . '$' . base64_encode($hash);
        $stmt->execute([$novo, $usuario['idUsuario']]);
        $contador++;
    }
    $pdo->commit();
    fwrite(STDOUT, "Migração de senhas concluída para $contador conta(s). Recomenda-se exigir troca de senha.\n");
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Falha na migração; confira conexão, permissão e esquema do banco.\n");
    exit(1);
}
