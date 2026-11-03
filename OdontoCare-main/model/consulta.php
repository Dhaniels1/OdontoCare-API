<?php

class Consulta
{
    private $idConsulta;
    private $dataConsulta;
    private $horarioConsulta;
    private $statusConsulta;
    private $observacoes;
    private $idUsuario;
    private $idPaciente;
    private $idProcedimento;

    public function __construct(
        $idConsulta,
        $dataConsulta,
        $horarioConsulta,
        $statusConsulta,
        $observacoes,
        $idUsuario,
        $idPaciente,
        $idProcedimento
    ) {
        $this->idConsulta = $idConsulta;
        $this->dataConsulta = $dataConsulta;
        $this->horarioConsulta = $horarioConsulta;
        $this->statusConsulta = $statusConsulta;
        $this->observacoes = $observacoes;
        $this->idUsuario = $idUsuario;
        $this->idPaciente = $idPaciente;
        $this->idProcedimento = $idProcedimento;
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