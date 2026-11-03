<?php

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

include_once "../model/consulta.php";
include_once "../model/pagamento.php";
include_once "../dao/ConsultaDao.php";
include_once "../dao/PagamentoDao.php";

if (($_POST["acao"] ?? null) === "excluir") {

    $consultaDao = new ConsultaDao();

    $consultaDao->delete(idExclusao());

    $_SESSION["mensagem"] = "Consulta cancelada e preservada no histórico.";

    header("location:../consulta.php");
    exit;
}

if (isset($_POST["btGravar"])) {

    $consulta = new Consulta(

        $_POST["txtIdConsulta"],
        $_POST["txtDataConsulta"],
        $_POST["txtHorarioConsulta"],
        $_POST["txtStatusConsulta"],
        $_POST["txtObservacoes"],
        $_POST["cbUsuario"],
        $_POST["cbPaciente"],
        $_POST["cbProcedimento"]

    );

    $consultaDao = new ConsultaDao();

    if(empty($_POST["txtIdConsulta"])){

        $consultaDao->create($consulta);

        $_SESSION["mensagem"] = "Consulta cadastrada com sucesso.";

    } else {

        $consultaDao->update($consulta);

        $_SESSION["mensagem"] = "Consulta atualizada com sucesso.";

    }

    header("location:../consulta.php");
    exit;
}