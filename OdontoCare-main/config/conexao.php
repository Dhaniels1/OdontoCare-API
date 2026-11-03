<?php
// Arquivo de compatibilidade sem credenciais: o cliente utiliza somente a API.
class conexao
{
    public static function conectar()
    {
        throw new LogicException('Acesso direto ao banco removido. Utilize ApiClient.');
    }
    public static function desconectar() {}
}
