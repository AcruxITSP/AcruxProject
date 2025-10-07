<?php
require '../../scriptsPhp/globalFunctions.php';
//verificarInicioSesion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Aulas</title>
    <link rel="stylesheet" href="../../styles/styles.css">
</head>

<body>
    <div class="forms-container">
        <div class="form-card">
            <h2>Registrar Aulas</h2>
            <form id="ingresarAulas-Form" action="scriptsPhp/scriptRegistrarAulas.php" method="post">

                <label for="codigo">Código:</label>
                <input type="text" id="codigo" name="codigo" required>

                <label for="piso">Piso:</label>
                <select id="piso" name="piso" required>
                    <option value="PB">PB</option>
                    <option value="1er">1er</option>
                    <option value="2do">2do</option>
                </select>

                <label for="proposito">Propósito:</label>
                <select id="proposito" name="proposito" required>
                    <option value="General">General</option>
                    <option value="Laboratorio">Laboratorio</option>
                    <option value="Taller">Taller</option>
                    <option value="Aula Informatica">Aula Informática</option>
                    <option value="Otro">Otro (Aun no implementado)</option>
                </select>

                <label for="cantidadSillas">Capacidad (cantidad de sillas):</label>
                <input type="number" id="cantidadSillas" name="cantidadSillas" required>

                <input type="submit" value="Registrar">
            </form>

            <a href="ver.php" class="volver"><i class="fa fa-arrow-left"></i> Volver</a>
        </div>
    </div>
</body>

</html>
