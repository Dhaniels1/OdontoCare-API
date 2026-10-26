<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "config/conexao.php";

$pdo = conexao::conectar();

$totalPacientes = $pdo->query("SELECT COUNT(*) FROM paciente")->fetchColumn();
$totalConsultas = $pdo->query("SELECT COUNT(*) FROM consulta")->fetchColumn();
$totalProcedimentos = $pdo->query("SELECT COUNT(*) FROM procedimento")->fetchColumn();
$totalPagamentos = $pdo->query("SELECT COUNT(*) FROM pagamento")->fetchColumn();
$totalMateriais = $pdo->query("SELECT COUNT(*) FROM material")->fetchColumn();

$consultasHoje = $pdo->query("
    SELECT COUNT(*) FROM consulta
    WHERE dataConsulta = CURDATE()
")->fetchColumn();

$valorRecebido = $pdo->query("
    SELECT SUM(valorTotal) FROM pagamento
    WHERE statusPagamento = 'Pago'
")->fetchColumn();
$totalMateriais = $pdo->query("
    SELECT COUNT(*) FROM material
")->fetchColumn();
$valorPendente = $pdo->query("
    SELECT SUM(valorTotal) FROM pagamento
    WHERE statusPagamento = 'Pendente'
")->fetchColumn();

$valorCancelado = $pdo->query("
    SELECT SUM(valorTotal) FROM pagamento
    WHERE statusPagamento = 'Cancelado'
")->fetchColumn();

$valorRecebido = $valorRecebido ?? 0;
$valorPendente = $valorPendente ?? 0;
$valorCancelado = $valorCancelado ?? 0;

$materiaisBaixoEstoque = $pdo->query("
    SELECT *
    FROM material
    WHERE quantidade <= estoqueMinimo
")->fetchAll(PDO::FETCH_ASSOC);

$totalBaixoEstoque = count($materiaisBaixoEstoque);

$dadosProcedimentos = $pdo->query("
    SELECT pr.nomeProcedimento, COUNT(*) AS total
    FROM consulta c
    INNER JOIN procedimento pr ON c.idProcedimento = pr.idProcedimento
    GROUP BY pr.nomeProcedimento
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$nomesProcedimentos = [];
$totaisProcedimentos = [];

foreach($dadosProcedimentos as $item){
    $nomesProcedimentos[] = $item["nomeProcedimento"];
    $totaisProcedimentos[] = $item["total"];
}

$proximasConsultas = $pdo->query("
    SELECT
        c.dataConsulta,
        c.horarioConsulta,
        c.statusConsulta,
        p.nome AS nomePaciente,
        pr.nomeProcedimento
    FROM consulta c
    INNER JOIN paciente p ON c.idPaciente = p.idPaciente
    INNER JOIN procedimento pr ON c.idProcedimento = pr.idProcedimento
    WHERE c.dataConsulta >= CURDATE()
    ORDER BY c.dataConsulta ASC, c.horarioConsulta ASC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OdontoCare</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container py-5">

    <h1 class="mb-4">🦷 Dashboard OdontoCare</h1>

    <div class="alert alert-success">
        Bem-vindo(a), <strong><?= $_SESSION["usuario"] ?></strong>
    </div>

    <div class="row">

        <div class="col-md mb-3">
            <a href="paciente.php" class="text-decoration-none text-dark">
                <div class="card shadow border-primary h-100">
                    <div class="card-body text-center">
                        <h6>👤 Pacientes</h6>
                        <h1><?= $totalPacientes ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md mb-3">
            <a href="consulta.php" class="text-decoration-none text-dark">
                <div class="card shadow border-success h-100">
                    <div class="card-body text-center">
                        <h6>📅 Consultas</h6>
                        <h1><?= $totalConsultas ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md mb-3">
            <a href="agenda.php" class="text-decoration-none text-dark">
                <div class="card shadow border-warning h-100">
                    <div class="card-body text-center">
                        <h6>⏰ Hoje</h6>
                        <h1><?= $consultasHoje ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md mb-3">
            <a href="procedimento.php" class="text-decoration-none text-dark">
                <div class="card shadow border-info h-100">
                    <div class="card-body text-center">
                        <h6>🦷 Procedimentos</h6>
                        <h1><?= $totalProcedimentos ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md mb-3">
            <a href="pagamento.php" class="text-decoration-none text-dark">
                <div class="card shadow border-danger h-100">
                    <div class="card-body text-center">
                        <h6>💳 Pagamentos</h6>
                        <h1><?= $totalPagamentos ?></h1>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md mb-3">
            <a href="material.php" class="text-decoration-none text-dark">
                <div class="card shadow border-secondary h-100">
                    <div class="card-body text-center">
                        <h6>📦 Materiais</h6>
                        <h1><?= $totalMateriais ?></h1>
                    </div>
                </div>
            </a>
        </div>

<div class="col-md mb-3">
    <div class="card shadow border-warning h-100">
        <div class="card-body text-center">
            <h6>⚠️ Baixo Estoque</h6>
            <h1><?= $totalBaixoEstoque ?></h1>
        </div>
    </div>
</div>

    <div class="row mt-4">

        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow border-0">
                <div class="card-body text-center">
                    <h5>💵 Recebido</h5>
                    <h2>R$ <?= number_format($valorRecebido, 2, ',', '.') ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card bg-warning shadow border-0">
                <div class="card-body text-center">
                    <h5>⏳ Pendente</h5>
                    <h2>R$ <?= number_format($valorPendente, 2, ',', '.') ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card bg-danger text-white shadow border-0">
                <div class="card-body text-center">
                    <h5>❌ Cancelado</h5>
                    <h2>R$ <?= number_format($valorCancelado, 2, ',', '.') ?></h2>
                </div>
            </div>
        </div>

    </div>

    <div class="row mt-4">

        <div class="col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="text-center mb-3">🦷 Procedimentos</h4>

                    <div style="height:320px; width:320px; margin:auto;">
                        <canvas id="graficoProcedimentos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="text-center mb-3">💰 Financeiro</h4>

                    <div style="height:320px; width:320px; margin:auto;">
                        <canvas id="graficoFinanceiro"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php if($totalBaixoEstoque > 0){ ?>

    <div class="card shadow mt-4 mb-5 border-warning">

        <div class="card-body">

            <h4 class="mb-3">
                ⚠️ Materiais com Baixo Estoque
            </h4>

            <table class="table table-striped table-bordered align-middle">

                <tr>
                    <th>Material</th>
                    <th>Quantidade Atual</th>
                    <th>Estoque Mínimo</th>
                    <th>Unidade</th>
                </tr>

                <?php foreach($materiaisBaixoEstoque as $material){ ?>

                <tr>
                    <td><?= $material["nomeMaterial"] ?></td>
                    <td>
                        <span class="badge bg-danger">
                            <?= $material["quantidade"] ?>
                        </span>
                    </td>
                    <td><?= $material["estoqueMinimo"] ?></td>
                    <td><?= $material["unidade"] ?></td>
                </tr>

                <?php } ?>

            </table>

        </div>

    </div>

    <?php } ?>

    <div class="card shadow mt-4 mb-5">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>📅 Próximas Consultas</h4>

                <a href="formConsulta.php" class="btn btn-primary">
                    Nova Consulta
                </a>
            </div>

            <table class="table table-striped table-bordered align-middle">

                <tr>
                    <th>Paciente</th>
                    <th>Procedimento</th>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Status</th>
                </tr>

                <?php foreach($proximasConsultas as $consulta){ ?>

                <tr>
                    <td><?= $consulta["nomePaciente"] ?></td>
                    <td><?= $consulta["nomeProcedimento"] ?></td>
                    <td><?= date("d/m/Y", strtotime($consulta["dataConsulta"])) ?></td>
                    <td><?= date("H:i", strtotime($consulta["horarioConsulta"])) ?></td>

                    <td>
                        <?php
                        if($consulta["statusConsulta"] == "Agendada"){
                            echo '<span class="badge bg-primary">Agendada</span>';
                        }
                        elseif($consulta["statusConsulta"] == "Concluída"){
                            echo '<span class="badge bg-success">Concluída</span>';
                        }
                        else{
                            echo '<span class="badge bg-danger">Cancelada</span>';
                        }
                        ?>
                    </td>
                </tr>

                <?php } ?>

            </table>

        </div>
    </div>

</div>

<footer class="text-center text-muted mt-5 mb-3">
    Sistema OdontoCare © 2026
</footer>

<script>
const graficoProcedimentos = document.getElementById('graficoProcedimentos');

new Chart(graficoProcedimentos, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($nomesProcedimentos) ?>,
        datasets: [{
            data: <?= json_encode($totaisProcedimentos) ?>,
            backgroundColor: [
                '#0d6efd',
                '#198754',
                '#ffc107',
                '#dc3545',
                '#0dcaf0',
                '#6f42c1',
                '#fd7e14',
                '#20c997'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<script>
const graficoFinanceiro = document.getElementById('graficoFinanceiro');

new Chart(graficoFinanceiro, {
    type: 'doughnut',
    data: {
        labels: [
            'Recebido',
            'Pendente',
            'Cancelado'
        ],
        datasets: [{
            data: [
                <?= $valorRecebido ?>,
                <?= $valorPendente ?>,
                <?= $valorCancelado ?>
            ],
            backgroundColor: [
                '#198754',
                '#ffc107',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>