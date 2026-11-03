<?php

session_start();
require_once __DIR__ . '/permissao.php';

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";
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

                <td><?= e($pagamento["idPagamento"]) ?></td>

                <td><?= e($pagamento["nomePaciente"]) ?></td>

                <td><?= e($pagamento["nomeProcedimento"]) ?></td>

                <td>
                    R$ <?= e(number_format($pagamento["valorTotal"], 2, ',', '.')) ?>
                </td>

                <td><?= e($pagamento["formaPagamento"]) ?></td>

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
                    <?= e(date("d/m/Y", strtotime($pagamento["dataPagamento"]))) ?>
                </td>

                <?php if(podeEditar()){ ?>
                    <td>

                        <a href="recibo.php?id=<?= e($pagamento["idPagamento"]) ?>"
                            class="btn btn-success btn-sm"
                            target="_blank">

                            🧾 Recibo

                        </a>

                        <a href="formPagamento.php?id=<?= e($pagamento["idPagamento"]) ?>"
                            class="btn btn-warning btn-sm">

                            Editar

                        </a>

                        <form action="controller/PagamentoController.php" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir este registro?')">
<?php csrfCampo(); ?>
<input type="hidden" name="acao" value="excluir">
<input type="hidden" name="id" value="<?= e($pagamento["idPagamento"]) ?>">
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