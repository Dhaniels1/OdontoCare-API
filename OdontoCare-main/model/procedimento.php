<?php

class Procedimento
{
    private $idProcedimento;
    private $nomeProcedimento;
    private $descricao;
    private $valorProcedimento;

    public function __construct($idProcedimento, $nomeProcedimento, $descricao, $valorProcedimento)
    {
        $this->idProcedimento = $idProcedimento;
        $this->nomeProcedimento = $nomeProcedimento;
        $this->descricao = $descricao;
        $this->valorProcedimento = $valorProcedimento;
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