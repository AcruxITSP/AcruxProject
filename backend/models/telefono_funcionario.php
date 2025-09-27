<?php
class Telefono_funcionario {
    public int $id_tel;
    public String $telefono;
    public int $id_funcionario;

    function __construct(int $id_tel, String $telefono, int $id_funcionario)
    {
        $this->id_tel = $id_tel;
        $this->telefono = $telefono;
        $this->id_funcionario = $id_funcionario;
    }
}