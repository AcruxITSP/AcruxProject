<?php
class Computadora_software {
    public int $id_compu;
    public int $id_software;

    function __construct(int $id_compu, $id_software){
        $this->id_compu = $id_compu;
        $this->id_software = $id_software;
    }
}