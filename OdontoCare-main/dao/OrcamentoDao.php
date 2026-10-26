<?php

class OrcamentoDao
{
    public function create(Orcamento $orcamento)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "INSERT INTO orcamento
            (
                valorTotal,
                dataOrcamento,
                statusOrcamento,
                observacoes,
                idPaciente
            )
            VALUES
            (?,?,?,?,?)";

            $query = $pdo->prepare($sql);

            $query->execute([
                $orcamento->valorTotal,
                $orcamento->dataOrcamento,
                $orcamento->statusOrcamento,
                $orcamento->observacoes,
                $orcamento->idPaciente
            ]);

            conexao::desconectar();
            return true;

        } catch(PDOException $exception) {
            die("Erro ao cadastrar orçamento: " . $exception->getMessage());
        }
    }

    public function read()
    {
        try {
            $pdo = conexao::conectar();

            $sql = "SELECT
                        o.idOrcamento,
                        o.valorTotal,
                        o.dataOrcamento,
                        o.statusOrcamento,
                        o.observacoes,
                        p.nome AS nomePaciente
                    FROM orcamento o
                    INNER JOIN paciente p ON o.idPaciente = p.idPaciente
                    ORDER BY o.dataOrcamento DESC";

            $resultado = $pdo->query($sql);

            $lista = [];

            foreach($resultado as $linha){
                $lista[] = $linha;
            }

            conexao::desconectar();
            return $lista;

        } catch(PDOException $exception) {
            die("Erro ao listar orçamentos: " . $exception->getMessage());
        }
    }

    public function readId($id)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "SELECT * FROM orcamento WHERE idOrcamento = ?";

            $query = $pdo->prepare($sql);
            $query->execute([$id]);

            $dados = $query->fetch(PDO::FETCH_ASSOC);

            conexao::desconectar();
            return $dados;

        } catch(PDOException $exception) {
            die("Erro ao buscar orçamento: " . $exception->getMessage());
        }
    }

    public function update(Orcamento $orcamento)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "UPDATE orcamento SET
                        valorTotal = ?,
                        dataOrcamento = ?,
                        statusOrcamento = ?,
                        observacoes = ?,
                        idPaciente = ?
                    WHERE idOrcamento = ?";

            $query = $pdo->prepare($sql);

            $query->execute([
                $orcamento->valorTotal,
                $orcamento->dataOrcamento,
                $orcamento->statusOrcamento,
                $orcamento->observacoes,
                $orcamento->idPaciente,
                $orcamento->idOrcamento
            ]);

            conexao::desconectar();
            return true;

        } catch(PDOException $exception) {
            die("Erro ao atualizar orçamento: " . $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "DELETE FROM orcamento WHERE idOrcamento = ?";

            $query = $pdo->prepare($sql);
            $query->execute([$id]);

            conexao::desconectar();
            return true;

        } catch(PDOException $exception) {
            die("Erro ao excluir orçamento: " . $exception->getMessage());
        }
    }
}