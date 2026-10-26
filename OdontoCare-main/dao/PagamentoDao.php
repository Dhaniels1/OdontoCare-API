<?php

class PagamentoDao
{
    public function create(Pagamento $pagamento)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "INSERT INTO pagamento
            (
                valorTotal,
                formaPagamento,
                statusPagamento,
                dataPagamento,
                idConsulta
            )
            VALUES
            (?,?,?,?,?)";

            $query = $pdo->prepare($sql);

            $query->execute([
                $pagamento->valorTotal,
                $pagamento->formaPagamento,
                $pagamento->statusPagamento,
                $pagamento->dataPagamento,
                $pagamento->idConsulta
            ]);

            conexao::desconectar();
            return true;

        } catch(PDOException $exception) {
            die("Erro ao cadastrar pagamento: " . $exception->getMessage());
        }
    }

    public function read()
    {
        try {
            $pdo = conexao::conectar();

            $sql = "SELECT
                        pg.idPagamento,
                        pg.valorTotal,
                        pg.formaPagamento,
                        pg.statusPagamento,
                        pg.dataPagamento,
                        pg.idConsulta,
                        p.nome AS nomePaciente,
                        pr.nomeProcedimento
                    FROM pagamento pg
                    INNER JOIN consulta c ON pg.idConsulta = c.idConsulta
                    INNER JOIN paciente p ON c.idPaciente = p.idPaciente
                    INNER JOIN procedimento pr ON c.idProcedimento = pr.idProcedimento
                    ORDER BY pg.dataPagamento DESC";

            $resultado = $pdo->query($sql);

            $lista = [];

            foreach($resultado as $linha) {
                $lista[] = $linha;
            }

            conexao::desconectar();
            return $lista;

        } catch(PDOException $exception) {
            die("Erro ao listar pagamentos: " . $exception->getMessage());
        }
    }

    public function readId($id)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "SELECT * FROM pagamento WHERE idPagamento = ?";

            $query = $pdo->prepare($sql);
            $query->execute([$id]);

            $dados = $query->fetch(PDO::FETCH_ASSOC);

            conexao::desconectar();
            return $dados;

        } catch(PDOException $exception) {
            die("Erro ao buscar pagamento: " . $exception->getMessage());
        }
    }

    public function update(Pagamento $pagamento)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "UPDATE pagamento SET
                        valorTotal = ?,
                        formaPagamento = ?,
                        statusPagamento = ?,
                        dataPagamento = ?,
                        idConsulta = ?
                    WHERE idPagamento = ?";

            $query = $pdo->prepare($sql);

            $query->execute([
                $pagamento->valorTotal,
                $pagamento->formaPagamento,
                $pagamento->statusPagamento,
                $pagamento->dataPagamento,
                $pagamento->idConsulta,
                $pagamento->idPagamento
            ]);

            conexao::desconectar();
            return true;

        } catch(PDOException $exception) {
            die("Erro ao alterar pagamento: " . $exception->getMessage());
        }
    }

    public function getByConsultaId($idConsulta)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "SELECT * FROM pagamento WHERE idConsulta = ?";

            $query = $pdo->prepare($sql);
            $query->execute([$idConsulta]);

            $dados = $query->fetch(PDO::FETCH_ASSOC);

            conexao::desconectar();
            return $dados;

        } catch(PDOException $exception) {
            die("Erro ao buscar pagamento da consulta: " . $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "DELETE FROM pagamento WHERE idPagamento = ?";
            $query = $pdo->prepare($sql);
            $query->execute([$id]);

            conexao::desconectar();
            return true;

        } catch(PDOException $exception) {
            die("Erro ao excluir pagamento: " . $exception->getMessage());
        }
    }
}