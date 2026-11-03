<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

include_once "../permissao.php";

if(!isset($_SESSION["usuario"])){
    header("location:../login.php");
    exit;
}

if(!podeEditar()){
    header("location:../index.php");
    exit;
}

validarPostCsrf();

include_once "../model/paciente.php";
include_once "../dao/PacienteDao.php";

if (($_POST["acao"] ?? null) === "excluir")
{
    $pacienteDao = new PacienteDao();

    $resultado = $pacienteDao->delete(idExclusao());

    header("location:../paciente.php");
    exit;
}

if (isset($_POST["btGravar"])) {

    $paciente = new Paciente(
        $_POST["txtIdPaciente"],
        $_POST["txtNome"],
        $_POST["txtCpf"],
        $_POST["txtTelefone"],
        $_POST["txtEmail"],
        $_POST["txtCep"],
        $_POST["txtLogradouro"],
        $_POST["txtNumero"],
        $_POST["txtBairro"],
        $_POST["txtCidade"],
        $_POST["txtEstado"],
        $_POST["txtDataNascimento"],
        $_POST["txtHistoricoOdontologico"],
        $_POST["txtAlergias"],
        $_POST["txtExames"],
        $_POST["txtTratamentosAnteriores"],
        $_POST["txtProntuarioClinicoDigital"]
    );

    $pacienteDao = new PacienteDao();

    if (empty($_POST["txtIdPaciente"])) {

        $resultado = $pacienteDao->create($paciente);

    } else {

        $resultado = $pacienteDao->update($paciente);

    }

    header("location:../paciente.php");
    exit;
}