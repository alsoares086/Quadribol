<?php

    require_once '../models/Usuario.php';
    require_once '../config/Conexao.php';

    class UsuarioControlador{

        public function cadastrar ($usuario){
            
            try{
            
            $conexao = new Conexao();
            $conn = $conexao->conectar();

            $senha_hash = password_hash($usuario->getSenha(),PASSWORD_DEFAULT);
        
            $sql = "INSERT INTO Usuarios(usuario,senha) 
                            VALUES (?,?)";
                
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(1,$usuario->getUsuario());
            $stmt->bindParam(2,$senha_hash);
            $stmt->execute(); 
                
            $conexao->fechar();

        }catch (Exception $e){
            throw $e;
        }
    }

}
?>