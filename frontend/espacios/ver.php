<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Ver Espacios</title>
</head>

<body id="body-ver-espacios">
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>

    <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    
    <main id="main-ver-espacios">
        <div id="lista-espacios">
            <div class="targeta-espacio">
                <div class="imagen-espacio">
                    <div class="marco-imagen-espacio"></div>
                </div>
                <div class="espacio-info">
                    <h1 class="nombre-espacio">Salon 5</h1>
                    <p class="reservado">Reservado</p>
                </div>
                <div class="container-btn-reservar">
                    <input class="btn-reservar" value="Reservar">
                </div>
            </div>
            <div class="targeta-espacio">
                <div class="imagen-espacio">
                    <div class="marco-imagen-espacio"></div>
                </div>
                <div class="espacio-info">
                    <h1 class="nombre-espacio">Salon 5</h1>
                    <p class="reservado">Reservado</p>
                </div>
                <div class="container-btn-reservar">
                    <input class="btn-reservar" value="Reservar">
                </div>
            </div>
            <div class="targeta-espacio">
                <div class="imagen-espacio">
                    <div class="marco-imagen-espacio"></div>
                </div>
                <div class="espacio-info">
                    <h1 class="nombre-espacio">Salon 5</h1>
                    <p class="reservado">Reservado</p>
                </div>
                <div class="container-btn-reservar">
                    <input class="btn-reservar" value="Reservar">
                </div>
            </div>
        </div>
        <button id="btn-agregar-espacio">Agregar Espacio</button>
    </main>
    <template id="tpl-targeta-espacio">
        <div class="targeta-espacio">
            <div class="imagen-espacio">
                <div class="marco-imagen-espacio"></div>
            </div>
            <div class="espacio-info">
                <h1 class="nombre-espacio"></h1>
                <p class="reservado"></p>
            </div>
            <div class="container-btn-reservar">
                <input class="btn-reservar" value="Reservar">
            </div>
        </div>
    </template>

    <script src="../scripts/espacios_agregar.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>