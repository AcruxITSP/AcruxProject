<?php
class Administrador {
    public int $id_administrador;
    public int $id_funcionario;

    function __construct(int $id_administrador, $id_funcionario){
        $this->id_administrador = $id_administrador;
        $this->id_funcionario = $id_funcionario;
    }
}