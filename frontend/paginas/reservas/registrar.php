<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar reservas</title>
    <link rel="stylesheet" href="../../styles/styles.css">
</head>

<body>
    <form>
        <label for="id_hora">Ingrese el id de la hora:</label>
        <input type="number" id="id_hora" name="id_hora" required><br><br>

        <label for="id_aula">Ingrese el id del aula:</label>
        <input type="number" id="id_aula" name="id_aula" required><br><br>

        <label for="id_funcionario">Ingrese el id del funcionario:</label>
        <input type="number" id="id_funcionario" name="id_funcionario" required><br><br>

        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha"><br><br>

        <input type="submit" value="Registrar">
    </form>
    <br>
    <a href="ver.php">Volver</a>
</body>

</html>