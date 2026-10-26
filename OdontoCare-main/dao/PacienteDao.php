<?php
require_once __DIR__ . '/../api/PacienteApi.php';
class PacienteDao
{
    private PacienteApi $api;
    public function __construct() { $this->api = new PacienteApi(); }
    private function payload(Paciente $p): array
    {
        $keys = ['nome','cpf','telefone','email','cep','logradouro','numero','bairro','cidade','estado',
            'dataNascimento','historicoOdontologico','alergias','exames','tratamentosAnteriores','prontuarioClinicoDigital'];
        $d = [];
        foreach ($keys as $key) $d[$key] = (string)($p->$key ?? '');
        return $d;
    }
    public function create(Paciente $p): bool
    {
        $r = $this->api->gravar(null, $this->payload($p));
        $_SESSION['erro'] = $r['sucesso'] ? null : $r['mensagem'];
        return $r['sucesso'];
    }
    public function read(): array
    {
        $r = $this->api->listar();
        if (!$r['sucesso']) { $_SESSION['erro'] = $r['mensagem']; return []; }
        return array_map(static fn($p) => (object)$p, $r['dados']);
    }
    public function readId($id): array|false
    {
        $r = $this->api->buscar($id);
        if (!$r['sucesso']) return false;
        $dados = $r['dados'];
        if (!empty($dados['dataNascimento'])) $dados['dataNascimento'] = substr($dados['dataNascimento'], 0, 10);
        return $dados;
    }
    public function update(Paciente $p): bool
    {
        $r = $this->api->gravar($p->idPaciente, $this->payload($p));
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
