<?php
class Adscripta {
    public int $id_adscripta;
    public int $id_funcionario;

    function __construct(int $id_adscripta, int $id_funcionario){
        $this->id_adscripta = $id_adscripta;
        $this->id_funcionario = $id_funcionario;
    }
}