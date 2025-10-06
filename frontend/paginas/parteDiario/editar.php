<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Registro del parte diario</title>
</head>

<body>
    <style>
        input:not([type = submit]) {
            width: 15rem;
        }
    </style>
    <h2>Editar un registro</h2>

    <form id="form-register-partediario">
        <input type="number" placeholder="Id de la adscripta"><br><br>

        <p>Fecha y hora actuales (En un futuro esto se hará automáticamente)</p>
        <input type="date"><br><br>
        
        <input type="time"><br><br>

        <p>Contenido del registro</p>
        <input type="text" placeholder="Escriba el contenido del registro">
        <br><br>
        <input type="submit">
    </form>
    <br>
    <a href="ver.php">Volver</a>
</body>

</html>