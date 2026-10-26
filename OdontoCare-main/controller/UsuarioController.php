<?php

session_start();

include_once "../permissao.php";

if(!isset($_SESSION["usuario"])){
    header("location:../login.php");
    exit;
}

if(!podeEditarUsuario()){
    header("location:../index.php");
    exit;
}

include_once "../model/usuario.php";
include_once "../dao/UsuarioDao.php";

$usuarioDao = new UsuarioDao();

if(isset($_POST["btGravar"])){

    $idUsuario = $_POST["txtIdUsuario"];
    $nome = $_POST["txtNome"];
    $email = $_POST["txtEmail"];
    $login = $_POST["txtLogin"];
    $senha = $_POST["txtSenha"];
    $tipoUsuario = $_POST["txtTipoUsuario"];

    $usuario = new Usuario(
        $idUsuario,
        $nome,
        $email,
        $login,
        $senha,
        $tipoUsuario
    );

    if(empty($idUsuario)){
        $usuarioDao->create($usuario);
    }else{
        $usuarioDao->update($usuario);
    }

    header("location:../usuario.php");
    exit;
}

if(isset($_GET["del"])){

    $usuarioDao->delete($_GET["del"]);

    header("location:../usuario.php");
    exit;
}