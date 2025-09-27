<?php
class Software {
    public int $id_software;
    public String $nombre;

    function __construct(int $id_software, string $nombre){
        $this->id_software = $id_software;
        $this->nombre = $nombre;
    }
}