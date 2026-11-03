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

include_once "dao/ConsultaDao.php";
include_once "api/ApiClient.php";

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

$rPacientes = ApiClient::requisitar('GET', 'api/pacientes');
$rUsuarios = ApiClient::requisitar('GET', 'api/usuarios');
$rProcedimentos = ApiClient::requisitar('GET', 'api/procedimentos');
$pacientes = $rPacientes['dados'] ?? [];
$usuarios = array_values(array_filter($rUsuarios['dados'] ?? [], static fn($u) => $u['tipoUsuario'] === 'Dentista'));
$procedimentos = $rProcedimentos['dados'] ?? [];

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
            <?= e($idConsulta == "" ? "Agendar Consulta" : "Editar Consulta") ?>
        </h1>

        <form action="controller/ConsultaController.php" method="POST" class="d-grid gap-3">
<?php csrfCampo(); ?>

            <input type="hidden" name="txtIdConsulta" value="<?= e($idConsulta) ?>">

            <div>
                <label>Paciente:</label>
                <select name="cbPaciente" class="form-control" required>
                    <option value="">Selecione</option>

                    <?php foreach ($pacientes as $paciente) { ?>
                        <option value="<?= e($paciente["idPaciente"]) ?>"
                            <?= e($idPaciente == $paciente["idPaciente"] ? "selected" : "") ?>>
                            <?= e($paciente["nome"]) ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <div>
                <label>Dentista:</label>
                <select name="cbUsuario" class="form-control" required>
                    <option value="">Selecione</option>

                    <?php foreach ($usuarios as $usuario) { ?>
                        <option value="<?= e($usuario["idUsuario"]) ?>"
                            <?= e($idUsuario == $usuario["idUsuario"] ? "selected" : "") ?>>
                            <?= e($usuario["nome"]) ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <div>
                <label>Procedimento:</label>
                <select name="cbProcedimento" class="form-control" required>
                    <option value="">Selecione</option>

                    <?php foreach ($procedimentos as $procedimento) { ?>
                        <option value="<?= e($procedimento["idProcedimento"]) ?>"
                            <?= e($idProcedimento == $procedimento["idProcedimento"] ? "selected" : "") ?>>
                            <?= e($procedimento["nomeProcedimento"]) ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <div>
                <label>Data da consulta:</label>
                <input type="date"
                       name="txtDataConsulta"
                       value="<?= e($dataConsulta) ?>"
                       class="form-control"
                       required>
            </div>

            <div>
                <label>Horário:</label>
                <input type="time"
                       name="txtHorarioConsulta"
                       value="<?= e($horarioConsulta) ?>"
                       class="form-control"
                       required>
            </div>

            <div>
                <label>Status:</label>
                <select name="txtStatusConsulta" class="form-control">

                    <option value="Agendada" <?= e($statusConsulta == "Agendada" ? "selected" : "") ?>>
                        Agendada
                    </option>

                    <option value="Concluída" <?= e($statusConsulta == "Concluída" ? "selected" : "") ?>>
                        Concluída
                    </option>

                    <option value="Cancelada" <?= e($statusConsulta == "Cancelada" ? "selected" : "") ?>>
                        Cancelada
                    </option>

                </select>
            </div>

            <div>
                <label>Observações:</label>
                <textarea name="txtObservacoes" class="form-control"><?= e($observacoes) ?></textarea>
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