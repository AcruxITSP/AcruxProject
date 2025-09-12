<!-- 
    ESTO ESTA INCOMPLETO

Este archivo se puede usar en varias otras paginas con el comando "require 'seleccionarFuncionarios.php';" 

"seleccionarFuncionarios.php" es un archivo php que al ser llamado con "require", despliega un elemento "tabla"
de HTML, en el que se listan todos los funcionarios, indicando su nombre, apellido, cedula y permite seleccionarlos
Esta tabla tambien es un formulario, permitiendo envir la seleccion del usuario a un script php para ser 
procesado depeniendo del contexto.
Por defecto, el formulario esta oculto. Es necesario usar la funcion "listarFuncionarios(phpDestino)" para
mostrarlo, ademas de indicar el nombre del script php al que se va a enviar el formulario
Nota: El nombre del php de destino debe estar entre comillas simples ('') o ser una variable de tipo string
-->

<style>
    /* Por defecto, los elementos de la clase ".oculto" no se verán */
    .oculto {
        display: none;
    }
</style>

<script>
    // Desplegar un formulario en el que el usuario puede seleccionar los funcionarios
    // Esto se va a usar principalmente en las paginas de los roles, para asignarle un rol a un funcionario
    function listarFuncionarios(phpDestino) {
        const formLayer = document.getElementById("formLayer");
        const formFunSelect = document.getElementById("form-FunSelect");

        console.log("conexion hecha");
        formLayer.classList.remove("oculto");

        formFunSelect.setAttribute("action", phpDestino);
    }
</script>

<div id="formLayer" class="oculto">
    <form id="form-FunSelect" method="post">
        <?php
        listarUsuario("Funcionario");
        ?>
    </form>
</div>