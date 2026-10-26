<?php
require_once __DIR__ . '/../api/ConsultaApi.php';
class ConsultaDao
{
    private ConsultaApi $api;
    public function __construct() { $this->api = new ConsultaApi(); }
    private function payload(Consulta $c): array
    {
        return [
            'dataConsulta' => substr((string)$c->dataConsulta, 0, 10),
            'horarioConsulta' => (string)$c->horarioConsulta,
            'statusConsulta' => (string)$c->statusConsulta,
            'observacoes' => (string)$c->observacoes,
            'idUsuario' => (int)$c->idUsuario,
            'idPaciente' => (int)$c->idPaciente,
            'idProcedimento' => (int)$c->idProcedimento
        ];
    }
    public function create(Consulta $c): bool
    {
        $r = $this->api->gravar(null, $this->payload($c));
        if (!$r['sucesso']) $_SESSION['erro'] = $r['mensagem'];
        return $r['sucesso'];
    }
    public function read(): array
    {
        $r = $this->api->listar();
        if (!$r['sucesso']) { $_SESSION['erro'] = $r['mensagem']; return []; }
        return $r['dados'];
    }
    public function readId($id): array|false
    {
        $r = $this->api->buscar($id);
        if (!$r['sucesso']) return false;
        $dados = $r['dados'];
        if (!empty($dados['dataConsulta'])) $dados['dataConsulta'] = substr($dados['dataConsulta'], 0, 10);
        return $dados;
    }
    public function update(Consulta $c): bool
    {
        $r = $this->api->gravar($c->idConsulta, $this->payload($c));
        if (!$r['sucesso']) $_SESSION['erro'] = $r['mensagem'];
        return $r['sucesso'];
    }
    public function delete($id): bool
    {
        $r = $this->api->excluir($id);
        if (!$r['sucesso']) $_SESSION['erro'] = $r['mensagem'];
        return $r['sucesso'];
    }
}
