<?php
require '../../scriptsPhp/globalFunctions.php';
//verificarInicioSesion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar funcionarios</title>
        <link rel="stylesheet" href="../../styles/styles.css">

</head>

<body>
    <h2>Registrar Aulas</h2>
    <form id="ingresarAulas-Form" action="scriptsPhp/scriptRegistrarAulas.php" method="post">

        <label for="codigo">Código:</label>
        <input type="text" id="codigo" name="codigo" required><br><br>

        <label for="piso">Piso:</label>
        <select id="piso" name="piso" required>
            <option value="PB">PB</option>
            <option value="1er">1er</option>
            <option value="2do">2do</option>
        </select><br><br>

        <label for="proposito">Propósito:</label>
        <select>
            <option value="General">General</option>
            <option value="Laboratorio">Laboratorio</option>
            <option value="Taller">Taller</option>
            <option value="Aula Informatica">Aula Informática</option>
            <option value="General">Otro (Aun no implementado)</option>
        </select><br><br>

        <label for="cantidadSillas">Capacidad (cantidad de sillas):</label>
        <input type="number" id="cantidadSillas" name="cantidadSillas" required><br><br>

        <input type="submit" value="Registrar">
    </form>
    <br>
    <a href="ver.php">Volver</a>
</body>

</html>