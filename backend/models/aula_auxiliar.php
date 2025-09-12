<?php
class Aula_auxiliar {
    public int $id_aula;
    public int $id_auxiliar;

    function __construct(int $id_aula, int $id_auxiliar){
        $this->id_aula = $id_aula;
        $this->id_auxiliar = $id_auxiliar;
    }
}