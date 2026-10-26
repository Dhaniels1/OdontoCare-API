<?php

class ConsultaDao
{
    public function create(Consulta $consulta)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "INSERT INTO consulta
            (
                dataConsulta,
                horarioConsulta,
                statusConsulta,
                observacoes,
                idUsuario,
                idPaciente,
                idProcedimento
            )
            VALUES
            (?,?,?,?,?,?,?)";

            $query = $pdo->prepare($sql);

            $query->execute([
                $consulta->dataConsulta,
                $consulta->horarioConsulta,
                $consulta->statusConsulta,
                $consulta->observacoes,
                $consulta->idUsuario,
                $consulta->idPaciente,
                $consulta->idProcedimento
            ]);

            conexao::desconectar();
            return true;

        } catch (PDOException $exception) {
            die("Erro ao cadastrar consulta: " . $exception->getMessage());
        }
    }

    public function read()
    {
        try {
            $pdo = conexao::conectar();

            $sql = "SELECT 
                        c.idConsulta,
                        c.dataConsulta,
                        c.horarioConsulta,
                        c.statusConsulta,
                        c.observacoes,
                        u.nome AS nomeUsuario,
                        p.nome AS nomePaciente,
                        pr.nomeProcedimento
                    FROM consulta c
                    INNER JOIN usuario u ON c.idUsuario = u.idUsuario
                    INNER JOIN paciente p ON c.idPaciente = p.idPaciente
                    INNER JOIN procedimento pr ON c.idProcedimento = pr.idProcedimento
                    ORDER BY c.dataConsulta, c.horarioConsulta";

            $resultado = $pdo->query($sql);

            $lista = [];

            foreach ($resultado as $linha) {
                $lista[] = $linha;
            }

            conexao::desconectar();
            return $lista;

        } catch (PDOException $exception) {
            die("Erro ao listar consultas: " . $exception->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $pdo = conexao::conectar();

            $sql = "DELETE FROM consulta WHERE idConsulta = ?";
            $query = $pdo->prepare($sql);
            $query->execute([$id]);

            conexao::desconectar();
            return true;

        } catch (PDOException $exception) {
            die("Erro ao excluir consulta: " . $exception->getMessage());
        }
    }
}