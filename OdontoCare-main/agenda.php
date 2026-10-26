<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "config/conexao.php";

$pdo = conexao::conectar();

$consultas = $pdo->query("
    SELECT
        c.idConsulta,
        c.dataConsulta,
        c.horarioConsulta,
        c.statusConsulta,
        p.nome AS nomePaciente,
        pr.nomeProcedimento
    FROM consulta c
    INNER JOIN paciente p ON c.idPaciente = p.idPaciente
    INNER JOIN procedimento pr ON c.idProcedimento = pr.idProcedimento
    ORDER BY c.dataConsulta ASC, c.horarioConsulta ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agenda - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container py-5">

    <h1 class="mb-4">📅 Agenda de Consultas</h1>

    <div class="card shadow">
        <div class="card-body">

            <table class="table table-striped table-hover">

                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Paciente</th>
                        <th>Procedimento</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach($consultas as $consulta){ ?>

                    <tr>

                        <td>
                            <?= date("d/m/Y", strtotime($consulta["dataConsulta"])) ?>
                        </td>

                        <td>
                            <?= date("H:i", strtotime($consulta["horarioConsulta"])) ?>
                        </td>

                        <td>
                            <?= $consulta["nomePaciente"] ?>
                        </td>

                        <td>
                            <?= $consulta["nomeProcedimento"] ?>
                        </td>

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

                </tbody>

            </table>

        </div>
    </div>

</div>

</body>
</html>