<?php
session_start();
require_once __DIR__ . "/../api/ApiClient.php";
if (!isset($_POST["btEntrar"])) {
    header("Location: ../login.php"); exit;
}
$resposta = ApiClient::requisitar("POST", "api/auth/login", [
    "identificador" => trim($_POST["txtLogin"] ?? ""),
    "senha" => (string) ($_POST["txtSenha"] ?? "")
]);
if (!$resposta["sucesso"] || empty($resposta["dados"]["token"])) {
    header("Location: ../login.php?erro=1"); exit;
}
$dados = $resposta["dados"];
$usuario = $dados["usuario"];
session_regenerate_id(true);
$_SESSION["jwt"] = $dados["token"];
$_SESSION["expiraEmUtc"] = $dados["expiraEmUtc"];
$_SESSION["usuario"] = $usuario["nome"];
$_SESSION["idUsuario"] = $usuario["idUsuario"];
$_SESSION["tipoUsuario"] = $usuario["tipoUsuario"];
header("Location: ../index.php"); exit;
