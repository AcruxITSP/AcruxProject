<?php
class Turno_grupo {
    public int $id_turno;
    public int $id_grupo;

    function __construct(int $id_turno, int $id_grupo){
        $this->id_turno = $id_turno;
        $this->id_grupo = $id_grupo;
    }
}