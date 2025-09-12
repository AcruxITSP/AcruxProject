<?php
class Hora {
    public int $id_hora;
    public int $id_intervalo;
    public int $id_dia;

    function __construct(int $id_hora, int $id_intervalo, int $id_dia){
        $this->id_hora = $id_hora;
        $this->id_intervalo = $id_intervalo;
        $this->id_dia = $id_dia;
    }
}