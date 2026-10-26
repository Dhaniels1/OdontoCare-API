<?php

/** Exibe (uma única vez) as mensagens de sucesso/erro guardadas na sessão pelos controllers. */
function exibirMensagens()
{
    if (isset($_SESSION["erro"])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION["erro"]) . '</div>';
        unset($_SESSION["erro"]);
    }

    if (isset($_SESSION["mensagem"])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION["mensagem"]) . '</div>';
        unset($_SESSION["mensagem"]);
    }
}
