<?php
class Telefono_tutor {
    public int $id_tel;
    public String $telefono;
    public String $nombreTutor;
    public int $id_estudiante;

    function __construct(int $id_tel, string $telefono, string $nombreTutor, int $id_estudiante){
        $this->id_tel = $id_tel;
        $this->telefono = $telefono;
        $this->nombreTutor = $nombreTutor;
        $this->id_estudiante = $id_estudiante;
    }
}