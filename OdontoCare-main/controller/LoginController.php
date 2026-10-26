<?php

session_start();

include_once "../config/conexao.php";

if(isset($_POST["btEntrar"])){

    $login = $_POST["txtLogin"];
    $senha = $_POST["txtSenha"];

    $pdo = conexao::conectar();

    $sql = "SELECT * FROM usuario WHERE login = ? AND senha = ?";

    $query = $pdo->prepare($sql);
    $query->execute([$login, $senha]);

    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if($usuario){

        $_SESSION["usuario"] = $usuario["nome"];
        $_SESSION["idUsuario"] = $usuario["idUsuario"];
        $_SESSION["tipoUsuario"] = $usuario["tipoUsuario"];

        header("location:../index.php");

    } else {

        header("location:../login.php?erro=1");

    }
}