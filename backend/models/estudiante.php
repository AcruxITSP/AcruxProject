<?php
class Estudiante {
    public int $id_estudiante;
//  public String $imgPerfilB64;  (Aun no esta en la BD)
    public String $nombre;
    public String $apellido;
    public String $DNI;
    public String $email;
    public String $contrasena;
    public String $reputacion;
    public int $id_grupo;

    function __construct(int $id_estudiante, /*?string $imgPerfilB64,*/ string $nombre, string $apellido, string $DNI, ?string $email, string $contrasena, string $reputacion, int $id_grupo)
    {
        $this->id_estudiante = $id_estudiante;
        //$this->imgPerfilB64 = $imgPerfilB64;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->DNI = $DNI;
        $this->email = $email;
        $this->contrasena = $contrasena;
        $this->reputacion = $reputacion;
        $this->id_grupo = $id_grupo;
    }
}