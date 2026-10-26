<?php

include_once __DIR__ . "/ApiClient.php";

/** Operações de consultas realizadas pela API (api/Consultas). */
class ConsultaApi
{
    public function listar()
    {
        $r = ApiClient::requisitar("GET", "api/Consultas");

        if ($r["sucesso"] && !is_array($r["dados"])) {
            $r["dados"] = [];
        }

        return $r;
    }

    public function buscar($id)
    {
        return ApiClient::requisitar("GET", "api/Consultas/" . intval($id));
    }

    /** Cria (sem id) ou atualiza (com id) uma consulta. */
    public function gravar($id, array $d)
    {
        $horario = trim($d["horarioConsulta"]);

        // <input type="time"> envia "HH:MM"; a API trabalha com TimeSpan "HH:MM:SS".
        if (preg_match('/^\d{2}:\d{2}$/', $horario)) {
            $horario .= ":00";
        }

        $corpo = [
            "dataConsulta" => trim($d["dataConsulta"]),
            "horarioConsulta" => $horario,
            "statusConsulta" => ApiClient::nuloSeVazio($d["statusConsulta"]),
            "observacoes" => ApiClient::nuloSeVazio($d["observacoes"]),
            "idUsuario" => intval($d["idUsuario"]),
            "idPaciente" => intval($d["idPaciente"]),
            "idProcedimento" => intval($d["idProcedimento"]),
        ];

        if (empty($id)) {
            return ApiClient::requisitar("POST", "api/Consultas", $corpo);
        }

        return ApiClient::requisitar("PUT", "api/Consultas/" . intval($id), $corpo);
    }

    public function excluir($id)
    {
        return ApiClient::requisitar("DELETE", "api/Consultas/" . intval($id));
    }
}
