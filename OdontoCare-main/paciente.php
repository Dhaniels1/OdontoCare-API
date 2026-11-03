<?php

session_start();
require_once __DIR__ . '/permissao.php';

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";
include_once "model/paciente.php";
include_once "dao/PacienteDao.php";

$pacienteDao = new PacienteDao();
$lista = $pacienteDao->read();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pacientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">
    
<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Pacientes</h1>

            <?php if(podeEditar()){ ?>
                <a href="formPaciente.php" class="btn btn-primary">
                    Novo Paciente
                </a>
            <?php } ?>
        </div>

        <table class="table table-striped table-bordered align-middle">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Telefone</th>

                <?php if(podeEditar()){ ?>
                    <th>Ações</th>
                <?php } ?>
            </tr>

            <?php foreach($lista as $paciente) { ?>
                <tr>
                    <td><?= e($paciente->idPaciente) ?></td>
                    <td><?= e($paciente->nome) ?></td>
                    <td><?= e($paciente->cpf) ?></td>
                    <td><?= e($paciente->telefone) ?></td>

                    <?php if(podeEditar()){ ?>
                        <td>
                            <a href="formPaciente.php?id=<?= e($paciente->idPaciente) ?>" class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form action="controller/PacienteController.php" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir este registro?')">
<?php csrfCampo(); ?>
<input type="hidden" name="acao" value="excluir">
<input type="hidden" name="id" value="<?= e($paciente->idPaciente) ?>">
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