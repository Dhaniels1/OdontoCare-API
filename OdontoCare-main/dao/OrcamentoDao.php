<?php
require_once __DIR__ . '/../api/DadosApi.php';
class OrcamentoDao
{
    private function payload(Orcamento $item): array
    {
        return [
            'valorTotal' => (float)$item->valorTotal,
            'dataOrcamento' => $item->dataOrcamento,
            'statusOrcamento' => $item->statusOrcamento,
            'observacoes' => $item->observacoes,
            'idPaciente' => (int)$item->idPaciente,
        ];
    }
    public function create(Orcamento $item): bool
    { dadosApi('POST', 'api/orcamentos', $this->payload($item)); return true; }
    public function read(): array
    {
        $dados = dadosApi('GET', 'api/orcamentos');
        return array_map('dataFormularioApi', $dados);
    }
    public function readId($id): array|false
    { return dataFormularioApi(dadosApi('GET', 'api/orcamentos/'.(int)$id, null, true)); }
    public function update(Orcamento $item): bool
    { dadosApi('PUT', 'api/orcamentos/'.(int)$item->idOrcamento, $this->payload($item)); return true; }
    public function delete($id): bool
    { dadosApi('DELETE', 'api/orcamentos/'.(int)$id); return true; }

}
