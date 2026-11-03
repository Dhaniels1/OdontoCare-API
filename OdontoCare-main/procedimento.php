<?php

session_start();
require_once __DIR__ . '/permissao.php';

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";
include_once "model/procedimento.php";
include_once "dao/ProcedimentoDao.php";

$procedimentoDao = new ProcedimentoDao();

$lista = $procedimentoDao->read();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procedimentos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h1>Procedimentos</h1>

            <?php if(podeEditar()){ ?>
                <a href="formProcedimento.php" class="btn btn-primary">
                    Novo Procedimento
                </a>
            <?php } ?>

        </div>

        <table class="table table-striped table-bordered">

            <tr>
                <th>ID</th>
                <th>Procedimento</th>
                <th>Descrição</th>
                <th>Valor</th>

                <?php if(podeEditar()){ ?>
                    <th>Ações</th>
                <?php } ?>
            </tr>

            <?php foreach($lista as $procedimento){ ?>

            <tr>

                <td><?= e($procedimento->idProcedimento) ?></td>

                <td><?= e($procedimento->nomeProcedimento) ?></td>

                <td><?= e($procedimento->descricao) ?></td>

                <td>
                    R$ <?= e(number_format($procedimento->valorProcedimento, 2, ',', '.')) ?>
                </td>

                <?php if(podeEditar()){ ?>
                    <td>

                        <form action="controller/ProcedimentoController.php" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir este registro?')">
<?php csrfCampo(); ?>
<input type="hidden" name="acao" value="excluir">
<input type="hidden" name="id" value="<?= e($procedimento->idProcedimento) ?>">
<button type="submit" class="btn btn-danger btn-sm">Excluir</button>
</form>

                    </td>
                <?php } ?>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>