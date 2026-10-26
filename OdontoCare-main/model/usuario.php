<?php

class Usuario
{
    private $idUsuario;
    private $nome;
    private $email;
    private $login;
    private $senha;
    private $tipoUsuario;

    public function __construct($idUsuario, $nome, $email, $login, $senha, $tipoUsuario)
    {
        $this->idUsuario = $idUsuario;
        $this->nome = $nome;
        $this->email = $email;
        $this->login = $login;
        $this->senha = $senha;
        $this->tipoUsuario = $tipoUsuario;
    }

    public function __get($atributo)
    {
        return $this->$atributo;
    }

    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }
}