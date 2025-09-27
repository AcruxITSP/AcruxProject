<?php
class Auxiliar {
    public int $id_auxiliar;
    public int $id_funcionario;

    function __construct(int $id_auxiliar, int $id_funcionario){
        $this->id_auxiliar = $id_auxiliar;
        $this->id_funcionario = $id_funcionario;
    }
}