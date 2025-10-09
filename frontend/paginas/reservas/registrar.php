<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar reservas</title>
    <link rel="stylesheet" href="../../styles/styles.css">
</head>

<body>
    <div class="forms-container">
        <div class="form-card">
            <h2>Registrar Reserva</h2>
            <form action="#" method="post">
                <label>Código del aula:</label>
                <select>
                    <option>2B</option>
                    <option>1A</option>
                    <option>3D</option>
                </select>

                <label>Hora de inicio:</label>
                <select>
                    <option>7:00 - 7:45</option>
                    <option>7:50 - 8:35</option>
                    <option>8:40 - 9:25</option>
                    <option>9:30 - 10:15</option>
                    <option>10:20 - 11:05</option>
                    <option>11:10 - 11:55</option>
                    <option>12:00 - 12:45</option>
                    <option>12:50 - 13:35</option>
                </select>

                <label>Hora de final:</label>
                <select>
                    <option>7:00 - 7:45</option>
                    <option>7:50 - 8:35</option>
                    <option>8:40 - 9:25</option>
                    <option>9:30 - 10:15</option>
                    <option>10:20 - 11:05</option>
                    <option>11:10 - 11:55</option>
                    <option>12:00 - 12:45</option>
                    <option>12:50 - 13:35</option>
                </select>

                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>
            </form>
            <button id="btn-submit-reserva">Registrar</button>

            <a href="../aulas/ver.php">Volver</a>
        </div>
    </div>
</body>

</html>