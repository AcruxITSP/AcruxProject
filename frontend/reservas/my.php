<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php require __DIR__ . '/../includes/header.php' ?>
</head>

<body id="body-reservas-my">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <main id="reservas-container">

    </main>

    <template id="template-div-reserva-recurso">
        <div name="div-reserva">
            <div class="div-reserva-top">
                <img name="image">
                <div class="titulos-reserva-recurso">
                    <h3 name="tipo">Control TV S5</h3>
                    <h5 name="espacio">Salon 5</h5>
                </div>
            </div>
            <p name="cantidad">Cantidad: 5</p>
            <div name="div-periodos"></div>
            <div name="botones">
                <button name="button-borrar">X</button>
            </div>
        </div>
    </template>

    <template id="template-div-reserva-espacio">
        <div name="div-reserva">
            <div class="div-reserva-top">
                <img name="image">
                <h3 name="espacio">Salon 5</h3>
            </div>
            <div name="div-periodos"></div>
            <div name="botones">
                <button name="button-borrar">X</button>
            </div>
        </div>
    </template>

    <template id="template-div-periodo">
        <div name="div-periodo">
            <p name="inicio">12:00</p>
            <p name="final">12:45</p>
        </div>
    </template>

    <script src="../scripts/reservas_my.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>