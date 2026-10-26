<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";
include_once "config/conexao.php";
include_once "dao/OrcamentoDao.php";

$orcamentoDao = new OrcamentoDao();

$lista = $orcamentoDao->read();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamentos - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h1>Orçamentos</h1>

            <?php if(podeEditar()){ ?>

                <a href="formOrcamento.php" class="btn btn-primary">
                    Novo Orçamento
                </a>

            <?php } ?>

        </div>

        <table class="table table-striped table-bordered align-middle">

            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Status</th>

                <?php if(podeEditar()){ ?>
                    <th>Ações</th>
                <?php } ?>
            </tr>

            <?php foreach($lista as $orcamento){ ?>

            <tr>

                <td><?= $orcamento["idOrcamento"] ?></td>

                <td><?= $orcamento["nomePaciente"] ?></td>

                <td>
                    R$ <?= number_format($orcamento["valorTotal"], 2, ',', '.') ?>
                </td>

                <td>
                    <?= date("d/m/Y", strtotime($orcamento["dataOrcamento"])) ?>
                </td>

                <td>
                    <?php
                        if($orcamento["statusOrcamento"] == "Aprovado"){
                            echo '<span class="badge bg-success">Aprovado</span>';
                        }
                        elseif($orcamento["statusOrcamento"] == "Pendente"){
                            echo '<span class="badge bg-warning text-dark">Pendente</span>';
                        }
                        else{
                            echo '<span class="badge bg-danger">Cancelado</span>';
                        }
                    ?>
                </td>

                <?php if(podeEditar()){ ?>

                    <td>

                        <a href="formOrcamento.php?id=<?= $orcamento["idOrcamento"] ?>"
                           class="btn btn-warning btn-sm">

                            Editar

                        </a>

                        <a href="controller/OrcamentoController.php?id=<?= $orcamento["idOrcamento"] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Deseja excluir este orçamento?')">

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