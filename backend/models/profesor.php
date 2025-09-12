<?php
class Profesor {
    public int $id_profesor;
    public String $fechaIngreso;
    public int $id_funcionario;

    function __construct(int $id_profesor, String $fechaIngreso, int $id_funcionario){
        $this->id_profesor = $id_profesor;
        $this->fechaIngreso = $fechaIngreso;
        $this->id_funcionario = $id_funcionario;
    }
}