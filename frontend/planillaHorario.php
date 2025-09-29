<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>Crear intervalos</h2>

    <form id="formIntervalos" method="post">
        <label for="horaIn">Hora entrada</label>
        <input type="number" id="horaIn" name="horaIn" required>:<input type="number" id="minsIn" name="minsIn" required><br><br>

        <label for="horaFin">Hora salida</label>
        <input type="number" id="horaFin" name="horaFin" required>:<input type="number" id="minsFin" name="minsFin" required><br><br>

        <label for="claseDuracion">Duracion de cada clase</label>
        <input type="number" id="claseDuracion" name="claseDuracion" required><br><br>

        <label for="recreo">Duracion del recreo</label>
        <input type="number" id="recreo" name="recreo" required><br><br>

        <h2>Definir Dias</h2>

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
            <p style="color: red;">Esto aun no funciona</p>
            <br>
            <a href="menuRecursos.php">Volver</a>

<script src="scripts/filtroCrearIntervalos.js"></script>
<script src="scripts/filtroDefinirDias.js"></script>
</body>

</html>