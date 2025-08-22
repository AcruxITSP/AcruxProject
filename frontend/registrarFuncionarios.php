<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar funcionarios</title>
</head>

<body>
    <style>
        input {
            margin-bottom: 1rem;
        }
    </style>
    <form id="registFun-Form" action="scriptRegistrarFun.php" method="post" target="_self">

        <h3>Registrar Funcionarios</h3>

        <label for="DNI">DNI:</label><br>
        <input type="text" id="DNI" name="DNI" required><br>

        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" required><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" required><br>

        <label for="email">Email (No es obligatorio):</label><br>
        <input type="email" id="email" name="email"><br>

        <label for="password">Contraseña (Por defecto será la cédula):</label><br>
        <!-- Tal vez haya que poner una opcion para ocultar la contraseña, si es que el usuario quiere-->
        <input type="text" id="password" name="password"><br>

        <input type="submit">

        
    </form>
</body>

</html>