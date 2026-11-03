<?php

session_start();
require_once __DIR__ . '/permissao.php';

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

                            <?= e($usuario["idUsuario"]) ?>

                        </td>

                        <td>

                            <?= e($usuario["nome"]) ?>

                        </td>

                        <td>

                            <?= e($usuario["email"]) ?>

                        </td>

                        <td>

                            <?= e($usuario["login"]) ?>

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
                                href="formUsuario.php?id=<?= e($usuario["idUsuario"]) ?>"
                                class="btn btn-warning btn-sm">

                                    Editar

                                </a>

                                <form action="controller/UsuarioController.php" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir este registro?')">
<?php csrfCampo(); ?>
<input type="hidden" name="acao" value="excluir">
<input type="hidden" name="id" value="<?= e($usuario["idUsuario"]) ?>">
<button type="submit" class="btn btn-danger btn-sm">Excluir</button>
</form>

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