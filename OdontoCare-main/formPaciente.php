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
            <?= e($idPaciente == "" ? "Cadastrar Paciente" : "Editar Paciente") ?>
        </h1>

        <form action="controller/PacienteController.php" method="POST" class="d-grid gap-3">
<?php csrfCampo(); ?>

            <input type="hidden" name="txtIdPaciente" value="<?= e($idPaciente) ?>">

            <div>
                <label>Nome:</label>
                <input type="text" name="txtNome" value="<?= e($nome) ?>" class="form-control" required>
            </div>

            <div>
                <label>CPF:</label>
                <input type="text" name="txtCpf" value="<?= e($cpf) ?>" class="form-control" required>
            </div>

            <div>
                <label>Telefone:</label>
                <input type="text" name="txtTelefone" value="<?= e($telefone) ?>" class="form-control">
            </div>

            <div>
                <label>Email:</label>
                <input type="email" name="txtEmail" value="<?= e($email) ?>" class="form-control">
            </div>

            <div>
                <label>CEP:</label>
                <input type="text" name="txtCep" value="<?= e($cep) ?>" class="form-control">
            </div>

            <div>
                <label>Logradouro:</label>
                <input type="text" name="txtLogradouro" value="<?= e($logradouro) ?>" class="form-control">
            </div>

            <div>
                <label>Número:</label>
                <input type="text" name="txtNumero" value="<?= e($numero) ?>" class="form-control">
            </div>

            <div>
                <label>Bairro:</label>
                <input type="text" name="txtBairro" value="<?= e($bairro) ?>" class="form-control">
            </div>

            <div>
                <label>Cidade:</label>
                <input type="text" name="txtCidade" value="<?= e($cidade) ?>" class="form-control">
            </div>

            <div>
                <label>Estado:</label>
                <input type="text" name="txtEstado" value="<?= e($estado) ?>" maxlength="2" class="form-control">
            </div>

            <div>
                <label>Data de nascimento:</label>
                <input type="date" name="txtDataNascimento" value="<?= e($dataNascimento) ?>" class="form-control">
            </div>

            <div>
                <label>Histórico odontológico:</label>
                <textarea name="txtHistoricoOdontologico" class="form-control"><?= e($historicoOdontologico) ?></textarea>
            </div>

            <div>
                <label>Alergias:</label>
                <textarea
                    name="txtAlergias"
                    class="form-control"
                    placeholder="Ex.: alergia à penicilina, anestésicos, látex..."><?= e($alergias) ?></textarea>
            </div>

            <div>
                <label>Exames:</label>
                <textarea
                    name="txtExames"
                    class="form-control"
                    placeholder="Ex.: radiografia, exame clínico, avaliação inicial..."><?= e($exames) ?></textarea>
            </div>

            <div>
                <label>Tratamentos anteriores:</label>
                <textarea
                    name="txtTratamentosAnteriores"
                    class="form-control"
                    placeholder="Ex.: canal, extração, limpeza, restauração..."><?= e($tratamentosAnteriores) ?></textarea>
            </div>

            <div>
                <label>Prontuário clínico digital:</label>
                <textarea
                    name="txtProntuarioClinicoDigital"
                    class="form-control"
                    placeholder="Anotações clínicas, evolução do tratamento e observações importantes..."><?= e($prontuarioClinicoDigital) ?></textarea>
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