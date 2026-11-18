<?php

include_once __DIR__ . "/ApiClient.php";

/** Operações de pacientes realizadas pela API (api/Pacientes). */
class PacienteApi
{
    public function listar()
    {
        $r = ApiClient::requisitar("GET", "api/Pacientes");

        if ($r["sucesso"] && !is_array($r["dados"])) {
            $r["dados"] = [];
        }

        return $r;
    }

    public function buscar($id)
    {
        return ApiClient::requisitar("GET", "api/Pacientes/" . intval($id));
    }

    /** Cria (sem id) ou atualiza (com id) um paciente. $d usa os nomes das colunas do formulário. */
    public function gravar($id, array $d)
    {
        $corpo = [
            "nome" => trim($d["nome"]),
            "cpf" => trim($d["cpf"]),
            "telefone" => ApiClient::nuloSeVazio($d["telefone"]),
            "email" => ApiClient::nuloSeVazio($d["email"]),
            "cep" => ApiClient::nuloSeVazio($d["cep"]),
            "logradouro" => ApiClient::nuloSeVazio($d["logradouro"]),
            "numero" => ApiClient::nuloSeVazio($d["numero"]),
            "bairro" => ApiClient::nuloSeVazio($d["bairro"]),
            "cidade" => ApiClient::nuloSeVazio($d["cidade"]),
            "estado" => ApiClient::nuloSeVazio($d["estado"]),
            "dataNascimento" => ApiClient::nuloSeVazio($d["dataNascimento"]),
        ];
        // Não envia nenhum campo clínico nos formulários da Secretária.
        foreach (["historicoOdontologico", "alergias", "exames", "tratamentosAnteriores", "prontuarioClinicoDigital"] as $campo) {
            if (array_key_exists($campo, $d)) {
                $corpo[$campo] = ApiClient::nuloSeVazio($d[$campo]);
            }
        }

        if (empty($id)) {
            return ApiClient::requisitar("POST", "api/Pacientes", $corpo);
        }

        return ApiClient::requisitar("PUT", "api/Pacientes/" . intval($id), $corpo);
    }

    public function excluir($id)
    {
        return ApiClient::requisitar("DELETE", "api/Pacientes/" . intval($id));
    }
}
