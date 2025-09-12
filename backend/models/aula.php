<?php
class Aula {
    public int $id_aula;
    public String $codigo;
    public String $piso;
    public String $proposito;
    public int $cantidadSillas;

    function __construct(int $id_aula, String $codigo, String $piso, String $proposito, int $cantidadSillas){
        $this->id_aula = $id_aula;
        $this->codigo = $codigo;
        $this->piso = $piso;
        $this->proposito = $proposito;
        $this->cantidadSillas = $cantidadSillas;
    }
}