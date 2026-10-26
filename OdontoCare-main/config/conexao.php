<?php
// Utilizado temporariamente pelos módulos financeiros/procedimentos legados.
class conexao
{
    private static $con = null;
    public static function conectar()
    {
        if (self::$con !== null) return self::$con;
        $host = getenv('ODONTOCARE_DB_HOST') ?: '127.0.0.1';
        $db = getenv('ODONTOCARE_DB_NAME') ?: 'odontocare';
        $user = getenv('ODONTOCARE_DB_USER');
        $password = getenv('ODONTOCARE_DB_PASS');
        if ($user === false || $password === false) throw new RuntimeException('Configure o acesso ao banco via variáveis de ambiente.');
        try {
            self::$con = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            error_log('OdontoCare: falha na conexão PDO.');
            throw new RuntimeException('Não foi possível conectar ao banco.');
        }
        return self::$con;
    }
    public static function desconectar() { self::$con = null; }
}
