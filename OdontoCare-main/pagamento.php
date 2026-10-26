<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";
include_once "config/conexao.php";
include_once "dao/PagamentoDao.php";

$pagamentoDao = new PagamentoDao();

$lista = $pagamentoDao->read();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagamentos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h1>Pagamentos</h1>

            <?php if(podeEditar()){ ?>
                <a href="formPagamento.php" class="btn btn-primary">
                    Novo Pagamento
                </a>
            <?php } ?>

        </div>

        <table class="table table-striped table-bordered align-middle">

            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Procedimento</th>
                <th>Valor</th>
                <th>Forma</th>
                <th>Status</th>
                <th>Data</th>

                <?php if(podeEditar()){ ?>
                    <th>Ações</th>
                <?php } ?>
            </tr>

            <?php foreach($lista as $pagamento){ ?>

            <tr>

                <td><?= $pagamento["idPagamento"] ?></td>

                <td><?= $pagamento["nomePaciente"] ?></td>

                <td><?= $pagamento["nomeProcedimento"] ?></td>

                <td>
                    R$ <?= number_format($pagamento["valorTotal"], 2, ',', '.') ?>
                </td>

                <td><?= $pagamento["formaPagamento"] ?></td>

                <td>
                    <?php
                        if($pagamento["statusPagamento"] == "Pago"){
                            echo '<span class="badge bg-success">Pago</span>';
                        }
                        elseif($pagamento["statusPagamento"] == "Pendente"){
                            echo '<span class="badge bg-warning text-dark">Pendente</span>';
                        }
                        else{
                            echo '<span class="badge bg-danger">Cancelado</span>';
                        }
                    ?>
                </td>

                <td>
                    <?= date("d/m/Y", strtotime($pagamento["dataPagamento"])) ?>
                </td>

                <?php if(podeEditar()){ ?>
                    <td>

                        <a href="recibo.php?id=<?= $pagamento["idPagamento"] ?>"
                            class="btn btn-success btn-sm"
                            target="_blank">

                            🧾 Recibo

                        </a>

                        <a href="formPagamento.php?id=<?= $pagamento["idPagamento"] ?>"
                            class="btn btn-warning btn-sm">

                            Editar

                        </a>

                        <a href="controller/PagamentoController.php?id=<?= $pagamento["idPagamento"] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Deseja excluir este pagamento?')">

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