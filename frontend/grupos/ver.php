<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>


<body id="body-grupos-ver" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-grupos-ver">
            <div class="scrollable-list" id="lista-targetas-grupos">
                
            </div>
            <div id="div-btn-agregar">
                <a href="crear.php" id="btn-agregar">Registrar grupo</a>
            </div>
        </main>
    </div>

    <template id="tpl-targeta-grupo">
        <div name="targeta-grupo">
            <div name="encabezado">
                <div class="marco-imagen"></div>
                <h1 class="nombre-grupo" name="nombre-grupo"></h1>
            </div>
            <div name="info-grupo">
                <p><strong>Adscripto/a: </strong></p>
                <p class="nombre-adscripta" name="nombre-adscripta">Silvana Salvatierra</p>

                <p><strong>Curso: </strong></p>
                <p class="nombre-curso" name="nombre-curso">Informática</p>
            </div>
            <div name="botones">
                <button name="editar"><i class="bi bi-pencil"></i></button>
                <button name="borrar"><i class="bi bi-trash-fill"></i></button>
            </div>
        </div>
    </template>

    <button class="btn-volver" onclick="history.back()" title="Volver">
        <i class="bi bi-arrow-left"></i>
    </button>

    <script src="../scripts/grupos_ver.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>