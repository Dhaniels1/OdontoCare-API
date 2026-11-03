<?php

class Orcamento
{
    private $idOrcamento;
    private $valorTotal;
    private $dataOrcamento;
    private $statusOrcamento;
    private $observacoes;
    private $idPaciente;

    public function __construct(
        $idOrcamento,
        $valorTotal,
        $dataOrcamento,
        $statusOrcamento,
        $observacoes,
        $idPaciente
    )
    {
        $this->idOrcamento = $idOrcamento;
        $this->valorTotal = $valorTotal;
        $this->dataOrcamento = $dataOrcamento;
        $this->statusOrcamento = $statusOrcamento;
        $this->observacoes = $observacoes;
        $this->idPaciente = $idPaciente;
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