<?php
include '../../util/sesiones.php';
if (!estaLogeado()) {
    header("Location: ../../cuenta/login.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Document</title>
</head>

<body id="body-recurso-interno-editar" class="menues-incluidos">
    <div id="menues">
        <?php include_once '../../includes/blockSidebarMenu.php' ?>
        <?php include_once '../../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main class="main-formulario-basico">
            <form class="formulario-basico">
                <h1>Editar Recurso Interno</h1>
                
                <label for="imagen">Imágen</label>
                <input type="file" name="imagen">

                <label for="tipo">Tipo</label>
                <input name="tipo" id="tipo">

                <label for="select-espacio">Espacio</label>
                <select name="select-espacio" id="select-espacio"></select>

                <label for="number">Cantidad</label>
                <input type="number" name="cantidad" min="0" max="500"></input>

                <input type="submit" value="Guardar">
            </form>
        </main>
    </div>

    <script src="../../scripts/recursos_internos_editar.js"></script>
    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>