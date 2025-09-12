<?php
class Dia {
    public int $id_dia;
    public String $nombre;

    function __construct(int $id_dia, string $nombre){
        $this->id_dia = $id_dia;
        $this->nombre = $nombre;
    }
}