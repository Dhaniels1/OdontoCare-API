<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";
include_once "config/conexao.php";
include_once "dao/ConsultaDao.php";

$consultaDao = new ConsultaDao();
$lista = $consultaDao->read();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consultas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h1>Consultas</h1>

            <?php if(podeEditar()){ ?>

                <a href="formConsulta.php" class="btn btn-primary">
                    Nova Consulta
                </a>

            <?php } ?>

        </div>

        <table class="table table-striped table-bordered align-middle">

            <tr>
                <th>ID</th>
                <th>Paciente</th>
                <th>Dentista</th>
                <th>Procedimento</th>
                <th>Data</th>
                <th>Horário</th>
                <th>Status</th>

                <?php if(podeEditar()){ ?>
                    <th>Ações</th>
                <?php } ?>

            </tr>

            <?php foreach($lista as $consulta){ ?>

            <tr>

                <td><?= $consulta["idConsulta"] ?></td>
                <td><?= $consulta["nomePaciente"] ?></td>
                <td><?= $consulta["nomeUsuario"] ?></td>
                <td><?= $consulta["nomeProcedimento"] ?></td>

                <td>
                    <?= date("d/m/Y", strtotime($consulta["dataConsulta"])) ?>
                </td>

                <td>
                    <?= date("H:i", strtotime($consulta["horarioConsulta"])) ?>
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

                <?php if(podeEditar()){ ?>

                <td>

                    <a href="formConsulta.php?id=<?= $consulta["idConsulta"] ?>"
                       class="btn btn-warning btn-sm">

                        Editar

                    </a>

                    <a href="controller/ConsultaController.php?id=<?= $consulta["idConsulta"] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Deseja realmente excluir esta consulta?')">

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

