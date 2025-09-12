<?php
class Clase {
    public int $id_clase;
    public int $id_profesor;
    public int $id_materia;

    function __construct(int $id_clase, int $id_profesor, int $id_materia){
        $this->id_clase = $id_clase;
        $this->id_profesor = $id_profesor;
        $this->id_materia = $id_materia;
    }
}