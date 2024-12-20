<?php 

    class Time {

        private $nome;
        private $cor;
        private $vitoria;
        private $derrota;

        public function getNome(){
            return $this->nome;
        }

        public function setNome($nome){
            $this->nome = $nome;
        }

        public function getCor(){
            return $this->cor;
        }

        public function setCor($cor){
            $this->cor = $cor;
        }

        public function getVitoria(){
            return $this->vitoria;
        }

        public function seVitoria($vitoria){
            $this->vitoria = $vitoria;
        }

        public function getDerrota(){
            return $this->derrota;
        }

        public function setDerrota($derrota){
            $this->derrota = $derrota;
        }
    
    }

?>