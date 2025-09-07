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
    <title>Definir días de clase</title>
</head>
<body>
    <!-- Nota: Hay que poner un filtro para que el usuario este obligado a seleccionar al menos 1 dia -->
    <form id="formDias" action="scriptsPhp/scriptDefinirDias.php" method="post">
        <h3>Seleccione todos los días en los que se dictará clase durante la semana</h3>

        <input type="checkbox" id="Lunes" name="Lunes" value="true">
        <label for="Lunes">Lunes</label><br>

        <input type="checkbox" id="Martes" name="Martes" value="true">
        <label for="Martes">Martes</label><br>

        <input type="checkbox" id="Miércoles" name="Miércoles" value="true">
        <label for="Miércoles">Miércoles</label><br>

        <input type="checkbox" id="Jueves" name="Jueves" value="true">
        <label for="Jueves">Jueves</label><br>

        <input type="checkbox" id="Viernes" name="Viernes" value="true">
        <label for="Viernes">Viernes</label><br>

        <input type="checkbox" id="Sábado" name="Sábado" value="true">
        <label for="Sábado">Sábado</label><br>

        <input type="checkbox" id="Domingo" name="Domingo" value="true">
        <label for="Domingo">Domingo</label><br><br>

        <input type="submit">
    </form>
    <script src="scripts/filtroDefinirDias.js"></script>
</body>
</html>