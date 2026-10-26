<?php
session_start();

if(isset($_SESSION["usuario"])){
    header("location:index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OdontoCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e7f1ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 15px;
        }

        .btn-login {
            border-radius: 30px;
            padding: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-5 col-lg-4">

            <div class="card shadow-lg login-card">

                <div class="card-body p-5">

                    <div class="logo-circle">
                        🦷
                    </div>

                    <h2 class="text-center fw-bold text-primary mb-2">
                        OdontoCare
                    </h2>

                    <p class="text-center text-muted mb-4">
                        Sistema de Gestão Odontológica
                    </p>

                    <?php if(isset($_GET["erro"])){ ?>

                        <div class="alert alert-danger text-center">
                            Login ou senha inválidos.
                        </div>

                    <?php } ?>

                    <form action="controller/LoginController.php" method="POST">

                        <div class="mb-3">
                            <label class="form-label">Login</label>
                            <input type="text"
                                   name="txtLogin"
                                   class="form-control form-control-lg"
                                   placeholder="Digite seu login"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Senha</label>
                            <input type="password"
                                   name="txtSenha"
                                   class="form-control form-control-lg"
                                   placeholder="Digite sua senha"
                                   required>
                        </div>

                        <button type="submit"
                                name="btEntrar"
                                class="btn btn-primary w-100 btn-login">

                            Entrar no sistema

                        </button>

                    </form>

                </div>

            </div>

            <p class="text-center text-white mt-3">
                OdontoCare © 2026
            </p>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>