<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../../styles/styles.css">

</head>

<body>
        <div class="forms-container">
            <!-- Crear Intervalos -->
            <div class="form-card">
                <h2>Crear intervalos</h2>
                <form id="formIntervalos" method="post">
                    <label for="horaIn">Hora entrada</label>
                    <input type="time" id="horaInicio" name="horaInicio" required><br><br>

                    <label for="horaFin">Hora salida</label>
                    <input type="time" id="horaFin" name="horaFin" required><br><br>

                    <label for="claseDuracion">Duración de cada clase (minutos)</label>
                    <input type="number" id="claseDuracion" name="claseDuracion" required><br><br>

                    <label for="recreo">Duración del recreo (minutos) </label>
                    <input type="number" id="recreo" name="recreo" required><br><br>
                </form>
            </div>

            <!-- Definir Días -->
            <div class="form-card">
                <h2>Definir Dias</h2>
                <form id="formDias" method="post">
                    <h3>Seleccione todos los días en los que se dictará clase durante la semana</h3>
                    <div class="checkbox-group two-columns">
                        <div class="column">
                            <label><input type="checkbox" id="Lunes" name="Lunes" value="true"> Lunes</label>
                            <label><input type="checkbox" id="Martes" name="Martes" value="true"> Martes</label>
                            <label><input type="checkbox" id="Miércoles" name="Miércoles" value="true"> Miércoles</label>
                            <label><input type="checkbox" id="Jueves" name="Jueves" value="true"> Jueves</label>
                            <label><input type="checkbox" id="Viernes" name="Viernes" value="true"> Viernes</label>
                            <label><input type="checkbox" id="Sábado" name="Sábado" value="true"> Sábado</label>
                            <label><input type="checkbox" id="Domingo" name="Domingo" value="true"> Domingo</label>
                        </div>
                    </div>
                    <input type="submit">
                    <p style="color: red;">Esto aun no funciona</p>
                    <br>
                    <a href="ver.php">Volver</a>
                </form>
            </div>
        </div>
    </body>

</html>