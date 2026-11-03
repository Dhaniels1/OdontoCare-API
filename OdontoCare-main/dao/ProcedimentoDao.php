<?php
require_once __DIR__ . '/../api/DadosApi.php';
class ProcedimentoDao
{
    private function payload(Procedimento $item): array
    {
        return [
            'nomeProcedimento' => $item->nomeProcedimento,
            'descricao' => $item->descricao,
            'valorProcedimento' => (float)$item->valorProcedimento,
        ];
    }
    public function create(Procedimento $item): bool
    { dadosApi('POST', 'api/procedimentos', $this->payload($item)); return true; }
    public function read(): array
    {
        $dados = dadosApi('GET', 'api/procedimentos');
        return array_map(static fn($p) => new Procedimento($p['idProcedimento'], $p['nomeProcedimento'], $p['descricao'], $p['valorProcedimento']), $dados);
    }
    public function readId($id): array|false
    { return dataFormularioApi(dadosApi('GET', 'api/procedimentos/'.(int)$id, null, true)); }
    public function update(Procedimento $item): bool
    { dadosApi('PUT', 'api/procedimentos/'.(int)$item->idProcedimento, $this->payload($item)); return true; }
    public function delete($id): bool
    { dadosApi('DELETE', 'api/procedimentos/'.(int)$id); return true; }

}
