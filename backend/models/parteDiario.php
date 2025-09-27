<?php
class ParteDiario {
    public int $id_entrada;
    public String $accion;
    public String $fecha_hora;
    public int $id_adscripta;

    function __construct(int $id_entrada, string $accion, string $fecha_hora, ?int $id_adscripta){
        $this->id_entrada = $id_entrada;
        $this->accion = $accion;
        $this->fecha_hora = $fecha_hora;
        $this->id_adscripta = $id_adscripta;
    }
}