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

include_once "model/procedimento.php";
include_once "dao/ProcedimentoDao.php";

$idProcedimento = "";
$nomeProcedimento = "";
$descricao = "";
$valorProcedimento = "";

if(isset($_GET["id"])){

    $procedimentoDao = new ProcedimentoDao();

    $dados = $procedimentoDao->readId($_GET["id"]);

    if($dados){
        $idProcedimento = $dados["idProcedimento"];
        $nomeProcedimento = $dados["nomeProcedimento"];
        $descricao = $dados["descricao"];
        $valorProcedimento = $dados["valorProcedimento"];
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procedimento - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h1 class="mb-4">
            <?= e($idProcedimento == "" ? "Cadastrar Procedimento" : "Editar Procedimento") ?>
        </h1>

        <form action="controller/ProcedimentoController.php" method="POST" class="d-grid gap-3">
<?php csrfCampo(); ?>

            <input type="hidden" name="txtIdProcedimento" value="<?= e($idProcedimento) ?>">

            <div>
                <label>Nome do Procedimento</label>
                <input
                    type="text"
                    name="txtNomeProcedimento"
                    value="<?= e($nomeProcedimento) ?>"
                    class="form-control"
                    required>
            </div>

            <div>
                <label>Descrição</label>
                <textarea
                    name="txtDescricao"
                    class="form-control"><?= e($descricao) ?></textarea>
            </div>

            <div>
                <label>Valor</label>
                <input
                    type="number"
                    step="0.01"
                    name="txtValorProcedimento"
                    value="<?= e($valorProcedimento) ?>"
                    class="form-control"
                    required>
            </div>

            <button
                type="submit"
                name="btGravar"
                class="btn btn-primary">

                Salvar

            </button>

            <a href="procedimento.php" class="btn btn-secondary">
                Voltar
            </a>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>