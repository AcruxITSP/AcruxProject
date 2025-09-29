<?php
class RecursoExterno {
    public int $id_recursoEx;
    public String $tipo;
    public bool $disponible;
    public int $id_aula;

    function __construct(int $id_recursoEx, string $tipo, bool $disponible, int $id_aula){
        $this->id_recursoEx = $id_recursoEx;
        $this->tipo = $tipo;
        $this->disponible = $disponible;
        $this->id_aula = $id_aula;
    }
}