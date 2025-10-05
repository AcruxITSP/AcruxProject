<?php
require '../scriptsPhp/globalFunctions.php';
//verificarInicioSesion();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar funcionarios</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>
    <div class="forms-container">
        <!-- Formulario Registrar Funcionarios -->
        <div class="form-card">
            <h2>Registrar Funcionarios</h2>
            <form id="registFun-Form" action="../scriptsPhp/scriptRegistrarFun.php" method="post" target="_self">

                <label for="DNI">DNI:</label>
                <input type="text" id="DNI" name="DNI" required>

                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" required>

                <label for="email">Email (No es obligatorio):</label>
                <input type="email" id="email" name="email">

                <label for="contrasena">Contraseña (Por defecto será la cédula):</label>
                <input type="text" id="contrasena" name="contrasena">

                <input type="submit" value="Registrar">
            </form>
            <a href="menurecursos.php">Volver</a>
        </div>

        <!-- Formulario Ver horario -->
        <div class="form-card">
            <h2>Ver horario de un funcionario</h2>
            <form id="form-FunSelect" method="get">
                <?php
                listarUsuario("Funcionario");
                ?>
                <input type="submit" value="Ver horario">
            </form>
        </div>
    </div>
</body>
</html>