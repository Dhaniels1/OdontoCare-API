<?php

/**
 * Cliente HTTP da API OdontoCare (ASP.NET Core).
 *
 * Todo acesso do PHP à API passa por aqui: se o formato das respostas mudar
 * (por exemplo, com a adoção de um ResultWrapper ou de JWT), basta adaptar este arquivo.
 *
 * Configuração: variável de ambiente ODONTOCARE_API_URL (padrão http://localhost:5240).
 */
class ApiClient
{
    private const URL_PADRAO = "http://localhost:5240";
    private const TIMEOUT_CONEXAO = 5;
    private const TIMEOUT_TOTAL = 15;

    public static function baseUrl()
    {
        $url = getenv("ODONTOCARE_API_URL");

        if ($url === false || trim($url) === "") {
            $url = self::URL_PADRAO;
        }

        return rtrim($url, "/");
    }

    /**
     * Envia uma requisição e devolve sempre o mesmo formato:
     * ["sucesso" => bool, "status" => int, "dados" => mixed, "mensagem" => string, "erros" => array]
     */
    public static function requisitar($metodo, $caminho, $corpo = null)
    {
        $url = self::baseUrl() . "/" . ltrim($caminho, "/");

        $cabecalhos = ["Accept: application/json"];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT_CONEXAO);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT_TOTAL);

        if ($corpo !== null) {
            $cabecalhos[] = "Content-Type: application/json";
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($corpo, JSON_UNESCAPED_UNICODE));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalhos);

        $resposta = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erroCurl = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);

        if ($erroCurl !== null) {
            return self::resultado(
                false,
                0,
                null,
                "Não foi possível se comunicar com a API em " . self::baseUrl() .
                ". Verifique se ela está em execução. (" . $erroCurl . ")"
            );
        }

        return self::interpretar($status, $resposta);
    }

    private static function interpretar($status, $resposta)
    {
        $json = null;

        if ($resposta !== false && trim($resposta) !== "") {
            $json = json_decode($resposta, true);
        }

        // Aceita também respostas no formato { success, message, data, errors } (ResultWrapper).
        if (is_array($json) && array_key_exists("success", $json) && array_key_exists("data", $json)) {
            return self::resultado(
                $json["success"] === true && $status >= 200 && $status < 300,
                $status,
                $json["data"],
                isset($json["message"]) ? (string) $json["message"] : "",
                isset($json["errors"]) && is_array($json["errors"]) ? $json["errors"] : []
            );
        }

        if ($status >= 200 && $status < 300) {
            return self::resultado(true, $status, $json, self::mensagemDe($json));
        }

        return self::resultado(false, $status, null, self::mensagemDeErro($status, $json), self::errosDe($json));
    }

    private static function mensagemDe($json)
    {
        if (is_array($json) && isset($json["mensagem"])) {
            return (string) $json["mensagem"];
        }

        return "";
    }

    // Erros de validação do ASP.NET: { "errors": { "Campo": ["mensagem"] } }
    private static function errosDe($json)
    {
        $lista = [];

        if (is_array($json) && isset($json["errors"]) && is_array($json["errors"])) {
            foreach ($json["errors"] as $mensagens) {
                foreach ((array) $mensagens as $mensagem) {
                    $lista[] = (string) $mensagem;
                }
            }
        }

        return $lista;
    }

    private static function mensagemDeErro($status, $json)
    {
        $mensagem = self::mensagemDe($json);

        if ($mensagem !== "") {
            return $mensagem;
        }

        $erros = self::errosDe($json);

        if (count($erros) > 0) {
            return implode(" ", $erros);
        }

        if ($status >= 500) {
            return "A API encontrou um erro interno (HTTP " . $status . "). Tente novamente.";
        }

        if ($status === 404) {
            return "Registro não encontrado.";
        }

        return "A API recusou a operação (HTTP " . $status . ").";
    }

    private static function resultado($sucesso, $status, $dados, $mensagem = "", $erros = [])
    {
        return [
            "sucesso" => $sucesso,
            "status" => $status,
            "dados" => $dados,
            "mensagem" => $mensagem,
            "erros" => $erros,
        ];
    }

    /** Converte "" em null, pois a API valida campos opcionais (e-mail, data) e não aceita texto vazio. */
    public static function nuloSeVazio($valor)
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim((string) $valor);

        return $valor === "" ? null : $valor;
    }
}
