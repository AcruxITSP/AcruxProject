<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso</title>
</head>

<body>
    <style>
        input:not([type=submit]) {
            width: 15rem;
        }
    </style>
    <h2>Editar la informacion del curso</h2>

    <form>
        <label for="curso-nombre">Nombre:</label>
        <input type="text" id="curso-nombre"><br><br>

        <label for="curso-duracionAnios">Duracion (años):</label>
        <input type="number" id="curso-duracionAnios"><br><br>

        <input type="submit">
    </form>
    <br>
    <a href="ver.php">Volver</a>
</body>

</html>