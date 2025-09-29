<?php
class Reserva {
    public int $id_reserva;
    public int $id_hora;
    public int $id_aula;
    public int $id_funcionario;
    public String $fecha;

    function __construct(int $id_reserva, int $id_hora, int $id_aula, int $id_funcionario, int $fecha){
        $this->id_reserva = $id_reserva;
        $this->id_hora = $id_hora;
        $this->id_aula = $id_aula;
        $this->id_funcionario = $id_funcionario;
        $this->fecha = $fecha;
    }
}