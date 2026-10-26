<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "config/conexao.php";

$pdo = conexao::conectar();

if(!isset($_GET["id"])){
    header("location:pagamento.php");
    exit;
}

$idPagamento = $_GET["id"];

$sql = "
    SELECT
        pg.idPagamento,
        pg.valorTotal,
        pg.formaPagamento,
        pg.statusPagamento,
        pg.dataPagamento,
        p.nome AS nomePaciente,
        p.cpf,
        pr.nomeProcedimento
    FROM pagamento pg
    INNER JOIN consulta c ON pg.idConsulta = c.idConsulta
    INNER JOIN paciente p ON c.idPaciente = p.idPaciente
    INNER JOIN procedimento pr ON c.idProcedimento = pr.idProcedimento
    WHERE pg.idPagamento = ?
";

$query = $pdo->prepare($sql);
$query->execute([$idPagamento]);

$recibo = $query->fetch(PDO::FETCH_ASSOC);

if(!$recibo){
    header("location:pagamento.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .recibo {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .recibo {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>

<body>

<div class="recibo">

    <div class="text-center mb-4">
        <h2>🦷 OdontoCare</h2>
        <h4>Recibo de Pagamento</h4>
        <hr>
    </div>

    <p>
        Recebemos de <strong><?= $recibo["nomePaciente"] ?></strong>,
        CPF <strong><?= $recibo["cpf"] ?></strong>,
        o valor de
        <strong>R$ <?= number_format($recibo["valorTotal"], 2, ',', '.') ?></strong>,
        referente ao procedimento odontológico
        <strong><?= $recibo["nomeProcedimento"] ?></strong>.
    </p>

    <p>
        Forma de pagamento:
        <strong><?= $recibo["formaPagamento"] ?></strong>
    </p>

    <p>
        Status:
        <strong><?= $recibo["statusPagamento"] ?></strong>
    </p>

    <p>
        Data do pagamento:
        <strong><?= date("d/m/Y", strtotime($recibo["dataPagamento"])) ?></strong>
    </p>

    <br><br>

    <p class="text-center">
        Muriaé - MG, <?= date("d/m/Y") ?>
    </p>

    <br><br>

    <div class="text-center">
        ______________________________________
        <br>
        Assinatura do responsável
    </div>

</div>

<div class="text-center no-print mb-5">
    <button onclick="window.print()" class="btn btn-primary">
        Imprimir Recibo
    </button>

    <a href="pagamento.php" class="btn btn-secondary">
        Voltar
    </a>
</div>

</body>
</html>