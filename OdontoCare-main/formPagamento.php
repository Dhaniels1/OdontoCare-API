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

include_once "model/pagamento.php";
include_once "dao/PagamentoDao.php";

require_once __DIR__ . '/api/DadosApi.php';

$idPagamento = "";
$valorTotal = "";
$formaPagamento = "";
$statusPagamento = "";
$dataPagamento = "";
$idConsulta = "";

if(isset($_GET["id"])){

    $pagamentoDao = new PagamentoDao();

    $dados = $pagamentoDao->readId($_GET["id"]);

    if($dados){
        $idPagamento = $dados["idPagamento"];
        $valorTotal = $dados["valorTotal"];
        $formaPagamento = $dados["formaPagamento"];
        $statusPagamento = $dados["statusPagamento"];
        $dataPagamento = $dados["dataPagamento"];
        $idConsulta = $dados["idConsulta"];
    }
}

$consultas = dadosApi('GET', 'api/consultas');

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h1 class="mb-4">
            <?= e($idPagamento == "" ? "Cadastrar Pagamento" : "Editar Pagamento") ?>
        </h1>

        <form action="controller/PagamentoController.php" method="POST" class="d-grid gap-3">
<?php csrfCampo(); ?>

            <input type="hidden" name="txtIdPagamento" value="<?= e($idPagamento) ?>">

            <div>
                <label>Consulta</label>

                <select name="cbConsulta" class="form-control" required>

                    <option value="">Selecione</option>

                    <?php foreach($consultas as $consulta){ ?>

                    <option value="<?= e($consulta["idConsulta"]) ?>"
                        <?= e($idConsulta == $consulta["idConsulta"] ? "selected" : "") ?>>

                        <?= e($consulta["nomePaciente"]) ?>
                        -
                        <?= e($consulta["nomeProcedimento"]) ?>

                    </option>

                    <?php } ?>

                </select>

            </div>

            <div>
                <label>Valor</label>
                <input type="number"
                       step="0.01"
                       name="txtValorTotal"
                       value="<?= e($valorTotal) ?>"
                       class="form-control"
                       required>
            </div>

            <div>
                <label>Forma de Pagamento</label>

                <select name="txtFormaPagamento" class="form-control">

                    <option value="Pix" <?= e($formaPagamento == "Pix" ? "selected" : "") ?>>
                        Pix
                    </option>

                    <option value="Dinheiro" <?= e($formaPagamento == "Dinheiro" ? "selected" : "") ?>>
                        Dinheiro
                    </option>

                    <option value="Cartão de Débito" <?= e($formaPagamento == "Cartão de Débito" ? "selected" : "") ?>>
                        Cartão de Débito
                    </option>

                    <option value="Cartão de Crédito" <?= e($formaPagamento == "Cartão de Crédito" ? "selected" : "") ?>>
                        Cartão de Crédito
                    </option>

                </select>

            </div>

            <div>
                <label>Status</label>

                <select name="txtStatusPagamento" class="form-control">

                    <option value="Pendente" <?= e($statusPagamento == "Pendente" ? "selected" : "") ?>>
                        Pendente
                    </option>

                    <option value="Pago" <?= e($statusPagamento == "Pago" ? "selected" : "") ?>>
                        Pago
                    </option>

                    <option value="Cancelado" <?= e($statusPagamento == "Cancelado" ? "selected" : "") ?>>
                        Cancelado
                    </option>

                </select>

            </div>

            <div>
                <label>Data do Pagamento</label>

                <input type="date"
                       name="txtDataPagamento"
                       value="<?= e($dataPagamento) ?>"
                       class="form-control"
                       required>
            </div>

            <button type="submit"
                    name="btGravar"
                    class="btn btn-primary">

                Salvar Pagamento

            </button>

            <a href="pagamento.php" class="btn btn-secondary">
                Voltar
            </a>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>