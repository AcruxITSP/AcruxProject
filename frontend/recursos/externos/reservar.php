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
    <link rel="stylesheet" href="../../styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Document</title>
</head>
<body id="body-reservar-externo">
    <main id="main-reservar-externo">
        <h1>Reservar Recurso Externo</h1>
        <div id="top">
            <img id="imagen">
            <p id="tipo">Salon X</p>
            <p id="espacio"></p>
        </div>
        <form id="form" form-id="form-reservar-externo">
            <input type="number" id="cantidad-a-reservar" name="cantidad-a-reservar" min="1" placeholder="Cantidad a Reservar" required>
            <div id="checkboxes-intervalos">

            </div>
            <input type="submit">
        </form>

        <template id="template-checkbox-intervalo">
            <label name="label"><input type="checkbox" name="horas[]" value="0" disabled="false"><p name="text"></p></label>
        </template>
    </main>

    <script src="../../scripts/recursos_externos_reservar.js"></script>
</body>
</html>