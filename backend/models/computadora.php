<?php
class Computadora {
    public int $id_compu;
    public String $SO;
    public String $estado;
    public String $problema;
    public int $id_aula;

    function __construct(int $id_compu, string $SO, string $estado, string $problema, int $id_aula){
        $this->id_compu = $id_compu;
        $this->SO = $SO;
        $this->estado = $estado;
        $this->problema = $problema;
        $this->id_aula = $id_aula;
    }
}