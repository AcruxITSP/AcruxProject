<?php
class Bloque {
    public int $id_bloque;
    public int $id_grupo;
    public int $id_clase;
    public int $id_aula;
    public int $id_hora;

    function __construct(int $id_bloque, int $id_grupo, int $id_clase, int $id_aula, int $id_hora){
        $this->id_bloque = $id_bloque;
        $this->id_grupo = $id_grupo;
        $this->id_clase = $id_clase;
        $this->id_aula = $id_aula;
        $this->id_hora = $id_hora;
    }
}