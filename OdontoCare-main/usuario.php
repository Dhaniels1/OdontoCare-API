<?php

session_start();

if(!isset($_SESSION["usuario"])){
    header("location:login.php");
    exit;
}

include_once "permissao.php";
include_once "dao/UsuarioDao.php";

$usuarioDao = new UsuarioDao();

$lista = $usuarioDao->read();

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Usuários - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include_once "navbar.php"; ?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1>👥 Usuários</h1>

        <?php if(podeEditarUsuario()){ ?>

            <a href="formUsuario.php"
               class="btn btn-primary">

                ➕ Novo Usuário

            </a>

        <?php } ?>

    </div>

    <div class="card shadow">

        <div class="card-body">

            <table class="table table-hover align-middle">

                <thead class="table-light">

                <tr>

                    <th>ID</th>

                    <th>Nome</th>

                    <th>Email</th>

                    <th>Login</th>

                    <th>Perfil</th>

                    <?php if(podeEditarUsuario()){ ?>

                        <th width="180">

                            Ações

                        </th>

                    <?php } ?>

                </tr>

                </thead>

                <tbody>

                <?php foreach($lista as $usuario){ ?>

                    <tr>

                        <td>

                            <?= $usuario["idUsuario"] ?>

                        </td>

                        <td>

                            <?= $usuario["nome"] ?>

                        </td>

                        <td>

                            <?= $usuario["email"] ?>

                        </td>

                        <td>

                            <?= $usuario["login"] ?>

                        </td>

                        <td>

                            <?php

                            if($usuario["tipoUsuario"]=="Administrador"){

                                echo '<span class="badge bg-danger">Administrador</span>';

                            }

                            elseif($usuario["tipoUsuario"]=="Dentista"){

                                echo '<span class="badge bg-success">Dentista</span>';

                            }

                            else{

                                echo '<span class="badge bg-primary">Secretária</span>';

                            }

                            ?>

                        </td>

                        <?php if(podeEditarUsuario()){ ?>

                            <td>

                                <a
                                href="formUsuario.php?id=<?= $usuario["idUsuario"] ?>"
                                class="btn btn-warning btn-sm">

                                    Editar

                                </a>

                                <a
                                href="controller/UsuarioController.php?del=<?= $usuario["idUsuario"] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Deseja excluir este usuário?')">

                                    Excluir

                                </a>

                            </td>

                        <?php } ?>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>