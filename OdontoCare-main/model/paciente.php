<?php

class Paciente
{
    private $idPaciente;
    private $nome;
    private $cpf;
    private $telefone;
    private $email;
    private $cep;
    private $logradouro;
    private $numero;
    private $bairro;
    private $cidade;
    private $estado;
    private $dataNascimento;
    private $historicoOdontologico;
    private $alergias;
    private $exames;
    private $tratamentosAnteriores;
    private $prontuarioClinicoDigital;

    public function __construct(
        $idPaciente,
        $nome,
        $cpf,
        $telefone,
        $email,
        $cep,
        $logradouro,
        $numero,
        $bairro,
        $cidade,
        $estado,
        $dataNascimento,
        $historicoOdontologico,
        $alergias,
        $exames,
        $tratamentosAnteriores,
        $prontuarioClinicoDigital
    )
    {
        $this->idPaciente = $idPaciente;
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->email = $email;
        $this->cep = $cep;
        $this->logradouro = $logradouro;
        $this->numero = $numero;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->dataNascimento = $dataNascimento;
        $this->historicoOdontologico = $historicoOdontologico;
        $this->alergias = $alergias;
        $this->exames = $exames;
        $this->tratamentosAnteriores = $tratamentosAnteriores;
        $this->prontuarioClinicoDigital = $prontuarioClinicoDigital;
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