<?php
require_once __DIR__ . '/../api/DadosApi.php';
class PagamentoDao
{
    private function payload(Pagamento $item): array
    {
        return [
            'valorTotal' => (float)$item->valorTotal,
            'formaPagamento' => $item->formaPagamento,
            'statusPagamento' => $item->statusPagamento,
            'dataPagamento' => $item->dataPagamento,
            'idConsulta' => (int)$item->idConsulta,
        ];
    }
    public function create(Pagamento $item): bool
    { dadosApi('POST', 'api/pagamentos', $this->payload($item)); return true; }
    public function read(): array
    {
        $dados = dadosApi('GET', 'api/pagamentos');
        return array_map('dataFormularioApi', $dados);
    }
    public function readId($id): array|false
    { return dataFormularioApi(dadosApi('GET', 'api/pagamentos/'.(int)$id, null, true)); }
    public function update(Pagamento $item): bool
    { dadosApi('PUT', 'api/pagamentos/'.(int)$item->idPagamento, $this->payload($item)); return true; }
    public function delete($id): bool
    { dadosApi('DELETE', 'api/pagamentos/'.(int)$id); return true; }

    public function getByConsultaId($idConsulta)
    {
        $dados = dadosApi('GET', 'api/pagamentos?consultaId='.(int)$idConsulta);
        return $dados ? dataFormularioApi($dados[0]) : false;
    }

}
