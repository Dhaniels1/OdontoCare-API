<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";

if(!podeEditarUsuario()){
    header("location:index.php");
    exit;
}

include_once "config/conexao.php";
include_once "model/usuario.php";
include_once "dao/UsuarioDao.php";

$idUsuario = "";
$nome = "";
$email = "";
$login = "";
$senha = "";
$tipoUsuario = "";

if(isset($_GET["id"])){

    $usuarioDao = new UsuarioDao();

    $dados = $usuarioDao->readId($_GET["id"]);

    if($dados){
        $idUsuario = $dados["idUsuario"];
        $nome = $dados["nome"];
        $email = $dados["email"];
        $login = $dados["login"];
        $senha = $dados["senha"];
        $tipoUsuario = $dados["tipoUsuario"];
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuário - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container py-5">

    <div class="card shadow p-4">

        <h1 class="mb-4">
            <?= $idUsuario == "" ? "Cadastrar Usuário" : "Editar Usuário" ?>
        </h1>

        <form action="controller/UsuarioController.php" method="POST" class="d-grid gap-3">

            <input type="hidden" name="txtIdUsuario" value="<?= $idUsuario ?>">

            <div>
                <label>Nome</label>
                <input type="text" name="txtNome" value="<?= $nome ?>" class="form-control" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="txtEmail" value="<?= $email ?>" class="form-control">
            </div>

            <div>
                <label>Login</label>
                <input type="text" name="txtLogin" value="<?= $login ?>" class="form-control" required>
            </div>

            <div>
                <label>Senha</label>
                <input type="password" name="txtSenha" value="<?= $senha ?>" class="form-control" required>
            </div>

            <div>
                <label>Tipo de Usuário</label>

                <select name="txtTipoUsuario" class="form-control" required>

                    <option value="">Selecione</option>

                    <option value="Administrador" <?= $tipoUsuario == "Administrador" ? "selected" : "" ?>>
                        Administrador
                    </option>

                    <option value="Dentista" <?= $tipoUsuario == "Dentista" ? "selected" : "" ?>>
                        Dentista
                    </option>

                    <option value="Secretária" <?= $tipoUsuario == "Secretária" ? "selected" : "" ?>>
                        Secretária
                    </option>

                </select>
            </div>

            <button type="submit" name="btGravar" class="btn btn-primary">
                Salvar
            </button>

            <a href="usuario.php" class="btn btn-secondary">
                Voltar
            </a>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>