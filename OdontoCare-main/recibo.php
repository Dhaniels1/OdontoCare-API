<?php

session_start();
require_once __DIR__ . '/permissao.php';

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

require_once __DIR__ . '/api/DadosApi.php';
$idPagamento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idPagamento || $idPagamento < 1) { header('Location: pagamento.php'); exit; }
$dados = dadosApi('GET', 'api/pagamentos/'.$idPagamento.'/recibo', null, true);
$recibo = $dados === false ? false : array_merge(dataFormularioApi($dados['pagamento']), ['cpf'=>$dados['cpf']]);

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
        Recebemos de <strong><?= e($recibo["nomePaciente"]) ?></strong>,
        CPF <strong><?= e($recibo["cpf"]) ?></strong>,
        o valor de
        <strong>R$ <?= e(number_format($recibo["valorTotal"], 2, ',', '.')) ?></strong>,
        referente ao procedimento odontológico
        <strong><?= e($recibo["nomeProcedimento"]) ?></strong>.
    </p>

    <p>
        Forma de pagamento:
        <strong><?= e($recibo["formaPagamento"]) ?></strong>
    </p>

    <p>
        Status:
        <strong><?= e($recibo["statusPagamento"]) ?></strong>
    </p>

    <p>
        Data do pagamento:
        <strong><?= e(date("d/m/Y", strtotime($recibo["dataPagamento"]))) ?></strong>
    </p>

    <br><br>

    <p class="text-center">
        Muriaé - MG, <?= e(date("d/m/Y")) ?>
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
