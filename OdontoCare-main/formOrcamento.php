<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";

if(!podeEditar()){
    header("location:index.php");
    exit;
}

include_once "config/conexao.php";
include_once "dao/OrcamentoDao.php";

$pdo = conexao::conectar();

$idOrcamento = "";
$valorTotal = "";
$dataOrcamento = date("Y-m-d");
$statusOrcamento = "Pendente";
$observacoes = "";
$idPaciente = "";

$pacientes = $pdo->query("
    SELECT idPaciente, nome
    FROM paciente
    ORDER BY nome
");

if(isset($_GET["id"])){

    $orcamentoDao = new OrcamentoDao();

    $dados = $orcamentoDao->readId($_GET["id"]);

    if($dados){

        $idOrcamento = $dados["idOrcamento"];
        $valorTotal = $dados["valorTotal"];
        $dataOrcamento = $dados["dataOrcamento"];
        $statusOrcamento = $dados["statusOrcamento"];
        $observacoes = $dados["observacoes"];
        $idPaciente = $dados["idPaciente"];

    }

}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Orçamento</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

<div class="card shadow p-4">

<h2 class="mb-4">

<?= $idOrcamento == "" ? "Novo Orçamento" : "Editar Orçamento" ?>

</h2>

<form action="controller/OrcamentoController.php" method="POST">

<input type="hidden"
name="txtIdOrcamento"
value="<?= $idOrcamento ?>">

<div class="mb-3">

<label>Paciente</label>

<select
name="cbPaciente"
class="form-control"
required>

<option value="">Selecione</option>

<?php foreach($pacientes as $paciente){ ?>

<option
value="<?= $paciente["idPaciente"] ?>"
<?= $idPaciente == $paciente["idPaciente"] ? "selected" : "" ?>>

<?= $paciente["nome"] ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label>Valor</label>

<input
type="number"
step="0.01"
name="txtValorTotal"
class="form-control"
value="<?= $valorTotal ?>"
required>

</div>

<div class="mb-3">

<label>Data</label>

<input
type="date"
name="txtDataOrcamento"
class="form-control"
value="<?= $dataOrcamento ?>"
required>

</div>

<div class="mb-3">

<label>Status</label>

<select
name="txtStatusOrcamento"
class="form-control">

<option value="Pendente"
<?= $statusOrcamento=="Pendente"?"selected":"" ?>>

Pendente

</option>

<option value="Aprovado"
<?= $statusOrcamento=="Aprovado"?"selected":"" ?>>

Aprovado

</option>

<option value="Cancelado"
<?= $statusOrcamento=="Cancelado"?"selected":"" ?>>

Cancelado

</option>

</select>

</div>

<div class="mb-3">

<label>Observações</label>

<textarea
name="txtObservacoes"
class="form-control"
rows="4"><?= $observacoes ?></textarea>

</div>

<button
type="submit"
name="btGravar"
class="btn btn-primary">

Salvar

</button>

<a
href="orcamento.php"
class="btn btn-secondary">

Voltar

</a>

</form>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>