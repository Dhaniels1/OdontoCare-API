<?php

class Pagamento
{
    private $idPagamento;
    private $valorTotal;
    private $formaPagamento;
    private $statusPagamento;
    private $dataPagamento;
    private $idConsulta;

    public function __construct(
        $idPagamento,
        $valorTotal,
        $formaPagamento,
        $statusPagamento,
        $dataPagamento,
        $idConsulta
    ) {
        $this->idPagamento = $idPagamento;
        $this->valorTotal = $valorTotal;
        $this->formaPagamento = $formaPagamento;
        $this->statusPagamento = $statusPagamento;
        $this->dataPagamento = $dataPagamento;
        $this->idConsulta = $idConsulta;
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