<?php
class Turno_funcionario{
    public int $id_funcionario;
    public int $id_turno;

    function __construct(int $id_funcionario, int $id_turno){
        $this->id_funcionario = $id_funcionario;
        $this->id_turno = $id_turno;
    }
}