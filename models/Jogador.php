<?php 

    class Jogador {

        private $nome;
        private $time;
        private $posicao;
        private $agilidade;
        private $forca;
        private $velocidade;


        public function getNome(){
            return $this->nome;
        }

        public function setNome($nome){
            $this->nome = $nome;
        }
        
        public function getTime(){
            return $this->time;
        }

        public function setTime($time){
            $this->time = $time;
        }

        public function getPosicao(){
            return $this->posicao;
        }

        public function setPosicao($posicao){
            $this->posicao = $posicao;
        }

        public function getAgilidade(){
            return $this->agilidade;
        }

        public function setAgilidade($agilidade){
            $this->agilidade = $agilidade;
        }
        
        public function getForca(){
            return $this->forca;
        }

        public function setForca($time){
            $this->forca = $forca;
        }

        public function geVelocidade(){
            return $this->posicao;
        }

        public function setVelocidade($velocidade){
            $this->velocidade = $velocidade;
        }
    }

?>