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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Reserva de Recurso Externo</title>
</head>

<body id="body-reservar-externo" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-reservar-externo">
            <h1>Reservar Recurso Externo</h1>
            <div id="top">
                <img id="imagen" src="../../img/resource-icon.png">
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
                <label name="label"><input type="checkbox" name="horas[]" value="0" disabled="false">
                    <p name="text"></p>
                </label>
            </template>
        </main>
    </div>

    <script src="../../scripts/recursos_externos_reservar.js"></script>
    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>