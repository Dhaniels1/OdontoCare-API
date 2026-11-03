<?php

session_start();
require_once __DIR__ . '/permissao.php';

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

require_once __DIR__ . '/api/DadosApi.php';
$painel = dadosApi('GET', 'api/painel');
$totalPacientes = $painel['totalPacientes'];
$totalConsultas = $painel['totalConsultas'];
$totalProcedimentos = $painel['totalProcedimentos'];
$totalPagamentos = $painel['totalPagamentos'];
$totalMateriais = $painel['totalMateriais'];
$consultasHoje = $painel['consultasHoje'];
$valorRecebido = $painel['valorRecebido'];
$valorPendente = $painel['valorPendente'];
$valorCancelado = $painel['valorCancelado'];
$materiaisBaixoEstoque = $painel['materiaisBaixoEstoque'];
$totalBaixoEstoque = count($materiaisBaixoEstoque);
$nomesProcedimentos = array_column($painel['dadosProcedimentos'], 'nomeProcedimento');
$totaisProcedimentos = array_column($painel['dadosProcedimentos'], 'total');
$proximasConsultas = $painel['proximasConsultas'];

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
        Bem-vindo(a), <strong><?= e($_SESSION["usuario"]) ?></strong>
    </div>

    <div class="row">

        <div class="col-md mb-3">
            <a href="paciente.php" class="text-decoration-none text-dark">
                <div class="card shadow border-primary h-100">
                    <div class="card-body text-center">
                        <h6>👤 Pacientes</h6>
                        <h1><?= e($totalPacientes) ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md mb-3">
            <a href="consulta.php" class="text-decoration-none text-dark">
                <div class="card shadow border-success h-100">
                    <div class="card-body text-center">
                        <h6>📅 Consultas</h6>
                        <h1><?= e($totalConsultas) ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md mb-3">
            <a href="agenda.php" class="text-decoration-none text-dark">
                <div class="card shadow border-warning h-100">
                    <div class="card-body text-center">
                        <h6>⏰ Hoje</h6>
                        <h1><?= e($consultasHoje) ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md mb-3">
            <a href="procedimento.php" class="text-decoration-none text-dark">
                <div class="card shadow border-info h-100">
                    <div class="card-body text-center">
                        <h6>🦷 Procedimentos</h6>
                        <h1><?= e($totalProcedimentos) ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md mb-3">
            <a href="pagamento.php" class="text-decoration-none text-dark">
                <div class="card shadow border-danger h-100">
                    <div class="card-body text-center">
                        <h6>💳 Pagamentos</h6>
                        <h1><?= e($totalPagamentos) ?></h1>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md mb-3">
            <a href="material.php" class="text-decoration-none text-dark">
                <div class="card shadow border-secondary h-100">
                    <div class="card-body text-center">
                        <h6>📦 Materiais</h6>
                        <h1><?= e($totalMateriais) ?></h1>
                    </div>
                </div>
            </a>
        </div>

<div class="col-md mb-3">
    <div class="card shadow border-warning h-100">
        <div class="card-body text-center">
            <h6>⚠️ Baixo Estoque</h6>
            <h1><?= e($totalBaixoEstoque) ?></h1>
        </div>
    </div>
</div>

    <div class="row mt-4">

        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white shadow border-0">
                <div class="card-body text-center">
                    <h5>💵 Recebido</h5>
                    <h2>R$ <?= e(number_format($valorRecebido, 2, ',', '.')) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card bg-warning shadow border-0">
                <div class="card-body text-center">
                    <h5>⏳ Pendente</h5>
                    <h2>R$ <?= e(number_format($valorPendente, 2, ',', '.')) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card bg-danger text-white shadow border-0">
                <div class="card-body text-center">
                    <h5>❌ Cancelado</h5>
                    <h2>R$ <?= e(number_format($valorCancelado, 2, ',', '.')) ?></h2>
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
                    <td><?= e($material["nomeMaterial"]) ?></td>
                    <td>
                        <span class="badge bg-danger">
                            <?= e($material["quantidade"]) ?>
                        </span>
                    </td>
                    <td><?= e($material["estoqueMinimo"]) ?></td>
                    <td><?= e($material["unidade"]) ?></td>
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
                    <td><?= e($consulta["nomePaciente"]) ?></td>
                    <td><?= e($consulta["nomeProcedimento"]) ?></td>
                    <td><?= e(date("d/m/Y", strtotime($consulta["dataConsulta"]))) ?></td>
                    <td><?= e(date("H:i", strtotime($consulta["horarioConsulta"]))) ?></td>

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
        labels: <?= jsonScript($nomesProcedimentos) ?>,
        datasets: [{
            data: <?= jsonScript(array_map('intval', $totaisProcedimentos)) ?>,
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
                <?= jsonScript((float)$valorRecebido) ?>,
                <?= jsonScript((float)$valorPendente) ?>,
                <?= jsonScript((float)$valorCancelado) ?>
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
