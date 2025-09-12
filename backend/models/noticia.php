<?php
class Noticia {
    public int $id_noticia;
    public String $fecha_hora;
    public String $contenido;
    public int $id_adscripta;

    function __construct(int $id_noticia, String $fecha_hora, String $contenido, ?int $id_adscripta){
        $this->id_noticia = $id_noticia;
        $this->fecha_hora = $fecha_hora;
        $this->contenido = $contenido;
        $this->id_adscripta = $id_adscripta;
    }
}