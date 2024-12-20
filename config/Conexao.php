<?php

class Conexao {
    private $host = "localhost";
    private $nomeBanco = "quadribol";
    private $usuario = "root";
    private $senha = "";
    private $conexao;

    public function conectar() {
        $this->conexao = null;

        try {
            $this->conexao = new PDO(
                "mysql:host={$this->host};dbname={$this->nomeBanco}",
                $this->usuario,
                $this->senha
            );
            $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexao->exec("set names utf8");
        } catch (Exception $e) {
            throw new Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }

        return $this->conexao;
    }

    public function fechar() {
        $this->conexao = null;
    }
}
?>
