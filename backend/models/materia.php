<?php
class Materia {
    public int $id_materia;
    public String $nombre;

    function __construct(int $id_materia, string $nombre){
        $this->id_materia = $id_materia;
        $this->nombre = $nombre;
    }
}