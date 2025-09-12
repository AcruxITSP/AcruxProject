<?php
/*
    ESTO ESTA INCOMPLETO
*/
require 'scriptsPhp/globalFunctions.php';
session_start();
//verificarInicioSesion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auxiliares</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>

<body>
    <style>
        table {
            /* Quitar la searacion entre los datos de la tabla */
            border-collapse: collapse;
            min-width: 60%;
        }

        input,
        label {
            width: 100%;
            height: 100%;
            display: block;
        }

        td,
        th {
            border: 1px solid black;
            padding: 0.2rem 0.5rem 0.2rem 0.5rem;
        }

        td:not(.DNI) {
            user-select: none;
        }
    </style>
    <h2>Gestionar Auxiliares</h2>
    <?php
    listarUsuario("Auxiliar");
    require 'seleccionarFuncionarios.php';
    ?>
    <button onclick="listarFuncionarios('login.php')">Agregar</button>
    <button>Eliminar</button>
    <script src="scripts/funcionesGenerales.js"></script>
</body>

</html>