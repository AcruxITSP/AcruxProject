<?php
class RecursoInterno {
    public int $id_recursoIn;
    public String $tipo;
    public String $estado;
    public String $problema;
    public int $id_aula;

    function __construct(int $id_recursoIn, string $tipo, string $estado, string $problema, int $id_aula){
        $this->id_recursoIn = $id_recursoIn;
        $this->tipo = $tipo;
        $this->estado = $estado;
        $this->problema = $problema;
        $this->id_aula = $id_aula;
    }
}