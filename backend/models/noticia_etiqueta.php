<?php
class Noticia_etiqueta {
    public int $id_noticia;
    public int $id_etiqueta;

    function __construct(int $id_noticia, int $id_etiqueta){
        $this->id_noticia = $id_noticia;
        $this->id_etiqueta = $id_etiqueta;
    }
}