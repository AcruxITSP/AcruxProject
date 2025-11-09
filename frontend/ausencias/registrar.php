<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ausencias</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="menues-incluidos">
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
    <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>

    <div id="modal-registrar-ausencia">
        <form class="formulario-basico">
            <h1>Registrar Ausencia</h1>
            <label for="fechaInicio">Inicio <input type="date" id="fechaInicio"></label>
            <label for="fechaFin">Fin <input type="date" id="fechaFin"></label>

            <label>Hora</label>
            <label for="allDay" id="allday-label">Todo el día <input type="checkbox" id="allDay"></label>

            <div id="lista-intervalos">
                Aca van los intervalos
            </div>

            <div>
                <label>Motivo</label>
                <textarea id="input-motivo"></textarea>
            </div>

            <input type="submit" value="Registrar">
        </form>
    </div>

    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>