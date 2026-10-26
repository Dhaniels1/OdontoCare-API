<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";

if(!podeEditar()){
    header("location:index.php");
    exit;
}

include_once "config/conexao.php";
include_once "dao/ConsultaDao.php";

$pdo = conexao::conectar();

$idConsulta = "";
$idPaciente = "";
$idUsuario = "";
$idProcedimento = "";
$dataConsulta = "";
$horarioConsulta = "";
$statusConsulta = "Agendada";
$observacoes = "";

if(isset($_GET["id"])){

    $consultaDao = new ConsultaDao();

    $dados = $consultaDao->readId($_GET["id"]);

    if($dados){
        $idConsulta = $dados["idConsulta"];
        $idPaciente = $dados["idPaciente"];
        $idUsuario = $dados["idUsuario"];
        $idProcedimento = $dados["idProcedimento"];
        $dataConsulta = $dados["dataConsulta"];
        $horarioConsulta = $dados["horarioConsulta"];
        $statusConsulta = $dados["statusConsulta"];
        $observacoes = $dados["observacoes"];
    }
}

$pacientes = $pdo->query("SELECT * FROM paciente ORDER BY nome");
$usuarios = $pdo->query("SELECT * FROM usuario WHERE tipoUsuario = 'Dentista' ORDER BY nome");
$procedimentos = $pdo->query("SELECT * FROM procedimento ORDER BY nomeProcedimento");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consulta - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h1 class="mb-4">
            <?= $idConsulta == "" ? "Agendar Consulta" : "Editar Consulta" ?>
        </h1>

        <form action="controller/ConsultaController.php" method="POST" class="d-grid gap-3">

            <input type="hidden" name="txtIdConsulta" value="<?= $idConsulta ?>">

            <div>
                <label>Paciente:</label>
                <select name="cbPaciente" class="form-control" required>
                    <option value="">Selecione</option>

                    <?php foreach ($pacientes as $paciente) { ?>
                        <option value="<?= $paciente["idPaciente"] ?>"
                            <?= $idPaciente == $paciente["idPaciente"] ? "selected" : "" ?>>
                            <?= $paciente["nome"] ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <div>
                <label>Dentista:</label>
                <select name="cbUsuario" class="form-control" required>
                    <option value="">Selecione</option>

                    <?php foreach ($usuarios as $usuario) { ?>
                        <option value="<?= $usuario["idUsuario"] ?>"
                            <?= $idUsuario == $usuario["idUsuario"] ? "selected" : "" ?>>
                            <?= $usuario["nome"] ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <div>
                <label>Procedimento:</label>
                <select name="cbProcedimento" class="form-control" required>
                    <option value="">Selecione</option>

                    <?php foreach ($procedimentos as $procedimento) { ?>
                        <option value="<?= $procedimento["idProcedimento"] ?>"
                            <?= $idProcedimento == $procedimento["idProcedimento"] ? "selected" : "" ?>>
                            <?= $procedimento["nomeProcedimento"] ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <div>
                <label>Data da consulta:</label>
                <input type="date"
                       name="txtDataConsulta"
                       value="<?= $dataConsulta ?>"
                       class="form-control"
                       required>
            </div>

            <div>
                <label>Horário:</label>
                <input type="time"
                       name="txtHorarioConsulta"
                       value="<?= $horarioConsulta ?>"
                       class="form-control"
                       required>
            </div>

            <div>
                <label>Status:</label>
                <select name="txtStatusConsulta" class="form-control">

                    <option value="Agendada" <?= $statusConsulta == "Agendada" ? "selected" : "" ?>>
                        Agendada
                    </option>

                    <option value="Concluída" <?= $statusConsulta == "Concluída" ? "selected" : "" ?>>
                        Concluída
                    </option>

                    <option value="Cancelada" <?= $statusConsulta == "Cancelada" ? "selected" : "" ?>>
                        Cancelada
                    </option>

                </select>
            </div>

            <div>
                <label>Observações:</label>
                <textarea name="txtObservacoes" class="form-control"><?= $observacoes ?></textarea>
            </div>

            <button type="submit" name="btGravar" class="btn btn-primary">
                Salvar Consulta
            </button>

        </form>

        <a href="consulta.php" class="btn btn-secondary mt-3">
            Ver consultas
        </a>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>