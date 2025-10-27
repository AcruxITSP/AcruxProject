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

<body id="body-recurso-interno-editar">
    <?php include_once __DIR__ . '/../../includes/blockSidebarMenu.php' ?>
    <?php include_once __DIR__ . '/../../includes/blockTopHeader.php' ?>
    
    <main>
        <form id="form">
            <h1>Editar Recurso Interno</h1>
            <label for="imagen">Imagen</label>
            <input type="file" name="imagen">

            <label for="tipo">Tipo</label>
            <select name="tipo"></select>

            <label for="espacio">Espacio</label>
            <select name="espacio"></select>

            <label for="number">Cantidad</label>
            <input type="number" name="cantidad" min="0" max="500"></input>

            <input type="submit" value="Registrar Recurso">
        </form>
    </main>

    <script src="crear.js"></script>
    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>