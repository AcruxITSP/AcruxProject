<?php
require 'scriptsPhp/globalFunctions.php';
session_start();
//verificarInicioSesion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calcular Horarios</title>
</head>

<body>
    <form id="formIntervalos" action="scriptsPhp/scriptCrearIntervalos.php" method="post">
        <label for="horaIn">Hora entrada</label>
        <input type="number" id="horaIn" name="horaIn" required>:<input type="number" id="minsIn" name="minsIn" required><br><br>

        <label for="horaFin">Hora salida</label>
        <input type="number" id="horaFin" name="horaFin" required>:<input type="number" id="minsFin" name="minsFin" required><br><br>

        <label for="claseDuracion">Duracion de cada clase</label>
        <input type="number" id="claseDuracion" name="claseDuracion" required><br><br>

        <label for="recreo">Duracion del recreo</label>
        <input type="number" id="recreo" name="recreo" required><br><br>

        <input type="submit">
    </form>
    <script src="scripts/filtroCrearIntervalos.js"></script>
</body>

</html>