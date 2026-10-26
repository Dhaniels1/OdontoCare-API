<?php

if(session_status() == PHP_SESSION_NONE){
    session_start();
}

include_once "permissao.php";

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">

    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            🦷 OdontoCare
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menu">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="menu">

            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        📊 Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="paciente.php">
                        👤 Pacientes
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="consulta.php">
                        📅 Consultas
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="materiais.php">📦 Materiais</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="procedimento.php">
                        🦷 Procedimentos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="pagamento.php">
                        💳 Pagamentos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="orcamento.php">
                        📝 Orçamentos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="agenda.php">
                        🗓️ Agenda
                    </a>
                </li>

                <?php if(podeEditarUsuario()){ ?>

                <li class="nav-item">
                    <a class="nav-link" href="usuario.php">
                        👥 Usuários
                    </a>
                </li>

                <?php } ?>

                <li class="nav-item ms-3">

                    <span class="navbar-text text-white">

                        👋 <?= $_SESSION["usuario"] ?>

                    </span>

                </li>

                <li class="nav-item ms-2">

                    <a class="btn btn-warning btn-sm fw-bold"
                       href="logout.php">

                        🚪 Sair

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>

