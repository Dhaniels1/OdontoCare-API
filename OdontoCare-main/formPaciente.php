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
include_once "model/paciente.php";
include_once "dao/PacienteDao.php";

$idPaciente = "";
$nome = "";
$cpf = "";
$telefone = "";
$email = "";
$cep = "";
$logradouro = "";
$numero = "";
$bairro = "";
$cidade = "";
$estado = "";
$dataNascimento = "";
$historicoOdontologico = "";
$alergias = "";
$exames = "";
$tratamentosAnteriores = "";
$prontuarioClinicoDigital = "";

if (isset($_GET["id"])) {
    $pacienteDao = new PacienteDao();
    $dados = $pacienteDao->readId($_GET["id"]);

    if ($dados) {
        $idPaciente = $dados["idPaciente"];
        $nome = $dados["nome"];
        $cpf = $dados["cpf"];
        $telefone = $dados["telefone"];
        $email = $dados["email"];
        $cep = $dados["cep"];
        $logradouro = $dados["logradouro"];
        $numero = $dados["numero"];
        $bairro = $dados["bairro"];
        $cidade = $dados["cidade"];
        $estado = $dados["estado"];
        $dataNascimento = $dados["dataNascimento"];
        $historicoOdontologico = $dados["historicoOdontologico"];
        $alergias = $dados["alergias"];
        $exames = $dados["exames"];
        $tratamentosAnteriores = $dados["tratamentosAnteriores"];
        $prontuarioClinicoDigital = $dados["prontuarioClinicoDigital"];
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paciente - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h1 class="mb-4">
            <?= $idPaciente == "" ? "Cadastrar Paciente" : "Editar Paciente" ?>
        </h1>

        <form action="controller/PacienteController.php" method="POST" class="d-grid gap-3">

            <input type="hidden" name="txtIdPaciente" value="<?= $idPaciente ?>">

            <div>
                <label>Nome:</label>
                <input type="text" name="txtNome" value="<?= $nome ?>" class="form-control" required>
            </div>

            <div>
                <label>CPF:</label>
                <input type="text" name="txtCpf" value="<?= $cpf ?>" class="form-control" required>
            </div>

            <div>
                <label>Telefone:</label>
                <input type="text" name="txtTelefone" value="<?= $telefone ?>" class="form-control">
            </div>

            <div>
                <label>Email:</label>
                <input type="email" name="txtEmail" value="<?= $email ?>" class="form-control">
            </div>

            <div>
                <label>CEP:</label>
                <input type="text" name="txtCep" value="<?= $cep ?>" class="form-control">
            </div>

            <div>
                <label>Logradouro:</label>
                <input type="text" name="txtLogradouro" value="<?= $logradouro ?>" class="form-control">
            </div>

            <div>
                <label>Número:</label>
                <input type="text" name="txtNumero" value="<?= $numero ?>" class="form-control">
            </div>

            <div>
                <label>Bairro:</label>
                <input type="text" name="txtBairro" value="<?= $bairro ?>" class="form-control">
            </div>

            <div>
                <label>Cidade:</label>
                <input type="text" name="txtCidade" value="<?= $cidade ?>" class="form-control">
            </div>

            <div>
                <label>Estado:</label>
                <input type="text" name="txtEstado" value="<?= $estado ?>" maxlength="2" class="form-control">
            </div>

            <div>
                <label>Data de nascimento:</label>
                <input type="date" name="txtDataNascimento" value="<?= $dataNascimento ?>" class="form-control">
            </div>

            <div>
                <label>Histórico odontológico:</label>
                <textarea name="txtHistoricoOdontologico" class="form-control"><?= $historicoOdontologico ?></textarea>
            </div>

            <div>
                <label>Alergias:</label>
                <textarea
                    name="txtAlergias"
                    class="form-control"
                    placeholder="Ex.: alergia à penicilina, anestésicos, látex..."><?= $alergias ?></textarea>
            </div>

            <div>
                <label>Exames:</label>
                <textarea
                    name="txtExames"
                    class="form-control"
                    placeholder="Ex.: radiografia, exame clínico, avaliação inicial..."><?= $exames ?></textarea>
            </div>

            <div>
                <label>Tratamentos anteriores:</label>
                <textarea
                    name="txtTratamentosAnteriores"
                    class="form-control"
                    placeholder="Ex.: canal, extração, limpeza, restauração..."><?= $tratamentosAnteriores ?></textarea>
            </div>

            <div>
                <label>Prontuário clínico digital:</label>
                <textarea
                    name="txtProntuarioClinicoDigital"
                    class="form-control"
                    placeholder="Anotações clínicas, evolução do tratamento e observações importantes..."><?= $prontuarioClinicoDigital ?></textarea>
            </div>

            <button type="submit" name="btGravar" class="btn btn-primary">
                Salvar
            </button>

        </form>

        <a href="paciente.php" class="btn btn-secondary mt-3">
            Ver pacientes
        </a>

    </div>    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>