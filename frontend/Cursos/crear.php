<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php require __DIR__ . '/../systems/inputlist/ilists.php'; ?>
    <title>Crear Curso</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body id="body-crear-cursos" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-crear-cursos">
            <form class="formulario-basico">
                <h1>Crear Curso</h1>

                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" type="text" placeholder="Nombre">

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" placeholder="Descripcion"></textarea>

                <label for="select-materias">Materias</label>
                <div id="select-materias" class="select-checkboxes">
                    <input id="label-opcionesMaterias" class="label-opcionesCheckboxes" type="text" placeholder="Seleccione las materias" readonly>
                    <div id="opcionesMaterias" class="scrollable-list opcionesCheckboxes"></div>
                </div>

                <input type="submit" value="Registrar">
            </form>
        </main>
    </div>

    <script src="../scripts/cursos_crear.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>