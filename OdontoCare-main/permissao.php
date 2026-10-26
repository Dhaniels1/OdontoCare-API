<?php

if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["usuario"])) {
    if (empty($_SESSION["jwt"]) || empty($_SESSION["expiraEmUtc"]) ||
        strtotime($_SESSION["expiraEmUtc"]) <= time()) {
        unset($_SESSION["jwt"], $_SESSION["usuario"], $_SESSION["idUsuario"], $_SESSION["tipoUsuario"]);
        $url = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/controller/') ? '../login.php' : 'login.php';
        header('Location: ' . $url);
        exit;
    }
}


function podeEditar()
{
    return isset($_SESSION["tipoUsuario"]) &&
    in_array(
        $_SESSION["tipoUsuario"],
        ["Administrador", "Secretaria", "Secretária"]
    );
}

function podeEditarUsuario()
{
    return isset($_SESSION["tipoUsuario"]) &&
    $_SESSION["tipoUsuario"] === "Administrador";
}