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

include_once "../model/procedimento.php";
include_once "../dao/ProcedimentoDao.php";
include_once "../config/validacaoFinanceira.php";

if (($_POST["acao"] ?? null) === "excluir")
{
    $procedimentoDao = new ProcedimentoDao();

    $procedimentoDao->delete(idExclusao());

    header("location:../procedimento.php");
    exit;
}

if(isset($_POST["btGravar"]))
{
    $id = financeiroId('txtIdProcedimento', true);
    $nome = financeiroTexto('txtNomeProcedimento', 200, true);
    $descricao = financeiroTexto('txtDescricao', 16000);
    $valor = financeiroValor('txtValorProcedimento');
    if ($id !== null) financeiroRegistro('procedimento', 'idProcedimento', $id);
    $procedimento = new Procedimento(

        $id,
        $nome,
        $descricao,
        $valor

    );

    $procedimentoDao = new ProcedimentoDao();

    if(empty($_POST["txtIdProcedimento"])){

        $procedimentoDao->create($procedimento);

    } else {

        $procedimentoDao->update($procedimento);

    }

    header("location:../procedimento.php");
    exit;
}
