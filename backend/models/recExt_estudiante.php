<?php
class RecExt_estudiante {
    public int $id_registro;
    public String $accion;
    public String $fecha_hora;
    public int $id_recursoEx;
    public int $id_secretario;
    public int $id_estudiante;

    function __construct(int $id_registro, string $accion, string $fecha_hora, int $id_recursoEx, ?int $id_secretario, int $id_estudiante){
        $this->id_registro = $id_registro;
        $this->accion = $accion;
        $this->fecha_hora = $fecha_hora;
        $this->id_recursoEx = $id_recursoEx;
        $this->id_secretario = $id_secretario;
        $this->id_estudiante = $id_estudiante;
    }
}