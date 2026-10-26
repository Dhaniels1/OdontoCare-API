<?php
require_once __DIR__ . '/../api/ApiClient.php';
class UsuarioDao
{
    private static function payload(Usuario $u, bool $novo): array
    {
        $body = [
            'nome' => trim((string)$u->nome), 'email' => (string)$u->email,
            'login' => trim((string)$u->login), 'tipoUsuario' => (string)$u->tipoUsuario
        ];
        if ($novo || trim((string)$u->senha) !== '') $body['senha'] = (string)$u->senha;
        return $body;
    }
    public function read(): array
    {
        $r = ApiClient::requisitar('GET','api/usuarios');
        if (!$r['sucesso']) { $_SESSION['erro'] = $r['mensagem']; return []; }
        return $r['dados'];
    }
    public function readId($id): array|false
    {
        $r = ApiClient::requisitar('GET','api/usuarios/' . intval($id));
        return $r['sucesso'] ? $r['dados'] : false;
    }
    public function create(Usuario $u): bool
    {
        $r = ApiClient::requisitar('POST','api/auth/registro',self::payload($u, true));
        if (!$r['sucesso']) $_SESSION['erro'] = $r['mensagem'];
        return $r['sucesso'];
    }
    public function update(Usuario $u): bool
    {
        $r = ApiClient::requisitar('PUT','api/usuarios/' . intval($u->idUsuario),self::payload($u, false));
        if (!$r['sucesso']) $_SESSION['erro'] = $r['mensagem'];
        return $r['sucesso'];
    }
    public function delete($id): bool
    {
        $r = ApiClient::requisitar('DELETE','api/usuarios/' . intval($id));
        if (!$r['sucesso']) $_SESSION['erro'] = $r['mensagem'];
        return $r['sucesso'];
    }
}
