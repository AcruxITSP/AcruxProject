<?php
include '../../util/sesiones.php';
if(!estaLogeado())
{
    header("Location: ../../cuenta/login.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <main>
        <form id="form">
            <h1>Editar Recurso Externo</h1>
            <label for="imagen">Imagen</label>
            <input type="file" name="imagen">

            <label for="tipo">Tipo</label>
            <select name="tipo"></select>

            <label for="espacio">Espacio</label>
            <select name="espacio">
                <option value="0">No es especifico al espacio.</option>
            </select>

            <label for="number">Cantidad</label>
            <input type="number" name="cantidad" min="0" max="500"></input>

            <input type="submit" value="Registrar Recurso">
        </form>
    </main>

    <script src="../../scripts/recursos_externos_editar.js"></script>
</body>
</html>