<?php
class Telefono_estudiante {
    public int $id_tel;
    public String $telefono;
    public int $id_estudiante;

    function __construct(int $id_tel, string $telefono, int $id_estudiante){
        $this->id_tel = $id_tel;
        $this->telefono = $telefono;
        $this->id_estudiante = $id_estudiante;
    }
}