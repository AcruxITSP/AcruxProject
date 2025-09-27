<?php
class Funcionario {
    public int $id_funcionario;
//  public String $imgPerfilB64;  (Aun no esta en la BD)
    public String $nombre;
    public String $apellido;
    public String $DNI;
    public String $email;
    public String $contrasena;

    function __construct(int $id_funcionario, /*?string $imgPerfilB64,*/ string $nombre, string $apellido, string $DNI, ?string $email, string $contrasena)
    {
        $this->id_funcionario = $id_funcionario;
        //$this->imgPerfilB64 = $imgPerfilB64;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->DNI = $DNI;
        $this->email = $email;
        $this->contrasena = $contrasena;
    }
}