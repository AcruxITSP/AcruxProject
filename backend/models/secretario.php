<?php
class Secretario {
    public int $id_secretario;
    public int $id_funcionario;

    function __construct(int $id_secretario, int $id_funcionario){
        $this->id_secretario = $id_secretario;
        $this->id_funcionario = $id_funcionario;
    }
}