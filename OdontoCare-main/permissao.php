<?php

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