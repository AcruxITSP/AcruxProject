<?php
class Etiqueta {
    public int $id_etiqueta;
    public String $nombre;

    function __construct(int $id_etiqueta, String $nombre){
        $this->id_etiqueta = $id_etiqueta;
        $this->nombre = $nombre;
    }
}