<?php
include 'globalFunctions.php';
//verificarInicioSesion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar funcionarios</title>
</head>

<body>
    <style>
        input, select {
            margin-bottom: 1rem;
        }
    </style>
    <form id="ingresarAulas-Form" action="scriptRegistrarAulas.php" method="post">

        <h3>Registrar Aulas</h3>

        <label for="codigo">Código:</label><br>
        <input type="text" id="codigo" name="codigo"><br>

        <label>Piso:</label><br>
        <select name="piso" required>
            <option value="PB">PB</option>
            <option value="1er">1er</option>
            <option value="2do">2do</option>
        </select><br>

        <label>Propósito:</label><br>
        <select name="proposito" required>
            <option value="General">General</option>
            <option value="Laboratorio">Laboratorio</option>
            <option value="Taller">Taller</option>
            <option value="Aula Informatica">Aula Informática</option>
        </select><br>

        <label for="cantidadSillas">Capacidad (cantidad de sillas):</label><br>
        <input type="number" id="cantidadSillas" name="cantidadSillas" required><br>

        <input type="submit">
    </form>
</body>

</html>