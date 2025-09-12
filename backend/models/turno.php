<?php
class Turno {
    public int $id_turno;
    public String $turno;

    function __construct(int $id_turno, String $turno){
        $this->id_turno = $id_turno;
        $this->turno = $turno;
    }
}