<?php
class Auxiliar_cargo {
    public int $id_auxiliar;
    public int $id_cargo;

    function __construct(int $id_auxiliar, int $id_cargo){
        $this->id_auxiliar = $id_auxiliar;
        $this->id_cargo = $id_cargo;
    }
}