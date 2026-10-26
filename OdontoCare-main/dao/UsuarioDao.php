<?php

include_once __DIR__ . "/../config/conexao.php";
include_once __DIR__ . "/../model/usuario.php";

class UsuarioDao{

    public function create(Usuario $usuario){

        $sql = conexao::conectar()->prepare("
            INSERT INTO usuario
            (nome,email,login,senha,tipoUsuario)
            VALUES
            (?,?,?,?,?)
        ");

        $sql->bindValue(1,$usuario->__get("nome"));
        $sql->bindValue(2,$usuario->__get("email"));
        $sql->bindValue(3,$usuario->__get("login"));
        $sql->bindValue(4,$usuario->__get("senha"));
        $sql->bindValue(5,$usuario->__get("tipoUsuario"));

        return $sql->execute();
    }

    public function read(){

        $sql=conexao::conectar()->prepare("
            SELECT *
            FROM usuario
            ORDER BY nome
        ");

        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readId($id){

        $sql=conexao::conectar()->prepare("
            SELECT *
            FROM usuario
            WHERE idUsuario=?
        ");

        $sql->bindValue(1,$id);

        $sql->execute();

        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function update(Usuario $usuario){

        $sql=conexao::conectar()->prepare("
            UPDATE usuario SET

            nome=?,
            email=?,
            login=?,
            senha=?,
            tipoUsuario=?

            WHERE idUsuario=?
        ");

        $sql->bindValue(1,$usuario->__get("nome"));
        $sql->bindValue(2,$usuario->__get("email"));
        $sql->bindValue(3,$usuario->__get("login"));
        $sql->bindValue(4,$usuario->__get("senha"));
        $sql->bindValue(5,$usuario->__get("tipoUsuario"));
        $sql->bindValue(6,$usuario->__get("idUsuario"));

        return $sql->execute();
    }

    public function delete($id){

        $sql=conexao::conectar()->prepare("
            DELETE FROM usuario
            WHERE idUsuario=?
        ");

        $sql->bindValue(1,$id);

        return $sql->execute();
    }

}