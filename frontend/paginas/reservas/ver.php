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
                <label for="id_hora">ID de la hora:</label>
                <input type="number" id="id_hora" name="id_hora" placeholder="Ingrese el ID de la hora" required>

                <label for="id_aula">ID del aula:</label>
                <input type="number" id="id_aula" name="id_aula" placeholder="Ingrese el ID del aula" required>

                <label for="id_funcionario">ID del funcionario:</label>
                <input type="number" id="id_funcionario" name="id_funcionario" placeholder="Ingrese el ID del funcionario" required>

                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>

                <input type="submit" value="Registrar">
            </form>

            <a href="ver.php">Volver</a>
        </div>
    </div>
</body>

</html>
