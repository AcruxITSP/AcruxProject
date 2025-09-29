<?php
class Cargo {
    public int $id_cargo;
    public string $nombre;

    function __construct(int $id_cargo, string $nombre){
        $this->id_cargo = $id_cargo;
        $this->nombre = $nombre;
    }
}