<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php require __DIR__ . '/../systems/inputlist/ilists.php'; ?>
    <title>Editar Grupo</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body id="body-crear-grupos" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-crear-grupos">
            <form id="form-editar-grupo" class="formulario-basico">
                <h1>Editar Grupo</h1>

                <div class="codigo-grupo">
                    <label>Grupo</label>
                    <label>Nombre</label>
                    <input name="grado" type="text" placeholder="Grado" class="grado-grupo">
                    <input name="nombre" type="text" placeholder="Nombre" class="nombre-grupo">
                </div>

                <label for="select-curso">Curso</label>
                <select name="id_curso" id="select-curso"></select>

                <label for="select-adscrito">Adscrito</label>
                <select name="id_adscrito" id="select-adscrito"></select>

                <input type="submit" value="Guardar">
            </form>
        </main>
    </div>

    <script src="../scripts/grupos_editar.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>