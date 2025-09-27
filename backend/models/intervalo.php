<?php
class Intervalo {
    public int $id_intervalo;
    public String $entrada;
    public String $salida;

    function __construct(int $id_intervalo, string $entrada, string $salida){
        $this->id_intervalo = $id_intervalo;
        $this->entrada = $entrada;
        $this->salida = $salida;
    }
}