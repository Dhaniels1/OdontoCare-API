<?php

session_start();
require_once __DIR__ . '/permissao.php';

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";

if(!podeEditar()){
    header("location:index.php");
    exit;
}

include_once "dao/OrcamentoDao.php";

require_once __DIR__ . '/api/DadosApi.php';

$idOrcamento = "";
$valorTotal = "";
$dataOrcamento = date("Y-m-d");
$statusOrcamento = "Pendente";
$observacoes = "";
$idPaciente = "";

$pacientes = dadosApi('GET', 'api/pacientes');

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

<?= e($idOrcamento == "" ? "Novo Orçamento" : "Editar Orçamento") ?>

</h2>

<form action="controller/OrcamentoController.php" method="POST">
<?php csrfCampo(); ?>

<input type="hidden"
name="txtIdOrcamento"
value="<?= e($idOrcamento) ?>">

<div class="mb-3">

<label>Paciente</label>

<select
name="cbPaciente"
class="form-control"
required>

<option value="">Selecione</option>

<?php foreach($pacientes as $paciente){ ?>

<option
value="<?= e($paciente["idPaciente"]) ?>"
<?= e($idPaciente == $paciente["idPaciente"] ? "selected" : "") ?>>

<?= e($paciente["nome"]) ?>

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
value="<?= e($valorTotal) ?>"
required>

</div>

<div class="mb-3">

<label>Data</label>

<input
type="date"
name="txtDataOrcamento"
class="form-control"
value="<?= e($dataOrcamento) ?>"
required>

</div>

<div class="mb-3">

<label>Status</label>

<select
name="txtStatusOrcamento"
class="form-control">

<option value="Pendente"
<?= e($statusOrcamento=="Pendente"?"selected":"") ?>>

Pendente

</option>

<option value="Aprovado"
<?= e($statusOrcamento=="Aprovado"?"selected":"") ?>>

Aprovado

</option>

<option value="Cancelado"
<?= e($statusOrcamento=="Cancelado"?"selected":"") ?>>

Cancelado

</option>

</select>

</div>

<div class="mb-3">

<label>Observações</label>

<textarea
name="txtObservacoes"
class="form-control"
rows="4"><?= e($observacoes) ?></textarea>

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