<?php
class Grupo {
    public int $id_grupo;
    public String $designacion;
    public String $curso;
    public int $id_adscripta;

    function __construct(int $id_grupo, String $designacion, String $curso, int $id_adscripta){
        $this->id_grupo = $id_grupo;
        $this->designacion = $designacion;
        $this->curso = $curso;
        $this->id_adscripta = $id_adscripta;
    }
}