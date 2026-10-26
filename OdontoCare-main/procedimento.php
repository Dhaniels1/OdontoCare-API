<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";
include_once "config/conexao.php";
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

                <td><?= $procedimento->idProcedimento ?></td>

                <td><?= $procedimento->nomeProcedimento ?></td>

                <td><?= $procedimento->descricao ?></td>

                <td>
                    R$ <?= number_format($procedimento->valorProcedimento, 2, ',', '.') ?>
                </td>

                <?php if(podeEditar()){ ?>
                    <td>

                        <a href="controller/ProcedimentoController.php?id=<?= $procedimento->idProcedimento ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Deseja excluir este procedimento?')">

                            Excluir

                        </a>

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