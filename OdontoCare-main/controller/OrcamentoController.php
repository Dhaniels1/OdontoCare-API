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

include_once "../model/orcamento.php";
include_once "../dao/OrcamentoDao.php";
include_once "../config/validacaoFinanceira.php";

if (($_POST["acao"] ?? null) === "excluir")
{
    $orcamentoDao = new OrcamentoDao();

    $orcamentoDao->delete(idExclusao());

    header("location:../orcamento.php");
    exit;
}

if(isset($_POST["btGravar"]))
{
    $id = financeiroId('txtIdOrcamento', true);
    $valor = financeiroValor('txtValorTotal');
    $data = financeiroData('txtDataOrcamento');
    $status = financeiroOpcao('txtStatusOrcamento', ['Pendente', 'Aprovado', 'Cancelado']);
    $observacoes = financeiroTexto('txtObservacoes', 16000);
    $paciente = financeiroId('cbPaciente');
    financeiroRegistro('paciente', 'idPaciente', $paciente);
    if ($id !== null) financeiroRegistro('orcamento', 'idOrcamento', $id);
    $orcamento = new Orcamento(
        $id,
        $valor,
        $data,
        $status,
        $observacoes,
        $paciente
    );

    $orcamentoDao = new OrcamentoDao();

    if(empty($_POST["txtIdOrcamento"])){

        $orcamentoDao->create($orcamento);

    } else {

        $orcamentoDao->update($orcamento);

    }

    header("location:../orcamento.php");
    exit;
}
