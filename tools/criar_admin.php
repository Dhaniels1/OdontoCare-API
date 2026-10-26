<?php
// Primeira configuração, somente via terminal. Não manter senha em scripts/commits.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$host = getenv('ODONTOCARE_DB_HOST') ?: '127.0.0.1';
$name = getenv('ODONTOCARE_DB_NAME') ?: 'odontocare';
$user = getenv('ODONTOCARE_DB_USER');
$pass = getenv('ODONTOCARE_DB_PASS');
$login = getenv('ODONTOCARE_ADMIN_LOGIN');
$password = getenv('ODONTOCARE_ADMIN_PASSWORD');
$displayName = getenv('ODONTOCARE_ADMIN_NAME') ?: 'Administrador';
if ($user === false || $pass === false || $login === false || $password === false || strlen($password) < 12) {
    fwrite(STDERR, "Configure ODONTOCARE_DB_USER, ODONTOCARE_DB_PASS, ODONTOCARE_ADMIN_LOGIN e ODONTOCARE_ADMIN_PASSWORD (mín. 12 caracteres).\n"); exit(1);
}
try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->beginTransaction();
    $conta = $pdo->query("SELECT COUNT(*) FROM usuario WHERE tipoUsuario='Administrador' FOR UPDATE")->fetchColumn();
    if ((int)$conta > 0) {
        $pdo->rollBack();
        fwrite(STDERR, "Já há um administrador. Use o login existente ou a migração de senhas.\n"); exit(1);
    }
    $salt = random_bytes(16);
    $hash = hash_pbkdf2('sha256', $password, $salt, 210000, 32, true);
    $stored = 'pbkdf2-sha256$210000$' . base64_encode($salt) . '$' . base64_encode($hash);
    $stmt = $pdo->prepare("INSERT INTO usuario (nome,email,login,senha,tipoUsuario) VALUES (?,NULL,?,?,'Administrador')");
    $stmt->execute([$displayName, $login, $stored]);
    $pdo->commit();
    fwrite(STDOUT, "Conta de administrador inicial criada.\n");
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Falha ao criar administrador; confirme esquema e conexão.\n");
    exit(1);
}
