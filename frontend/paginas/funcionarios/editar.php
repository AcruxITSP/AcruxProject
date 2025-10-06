<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Funcionario</title>
</head>

<body>
    <style>
        input:not([type = submit]) {
            width: 15rem;
        }
    </style>
    <h2>Editar la informacion del funcionario</h2>

    <form id="registFun-Form" action="../scriptsPhp/scriptRegistrarFun.php" method="post" target="_self">

        <label for="DNI">DNI:</label>
        <input type="text" id="DNI" name="DNI" required><br><br>

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" required><br><br>

        <label for="email">Email (No es obligatorio):</label>
        <input type="email" id="email" name="email"><br><br>

        <label for="contrasena">Contraseña (Por defecto será la cédula):</label>
        <input type="text" id="contrasena" name="contrasena"><br><br>

        <input type="submit" value="Registrar">
    </form>
    <br>
    <a href="ver.php">Volver</a>
</body>

</html>