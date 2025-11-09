<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php require __DIR__ . '/../systems/inputlist/ilists.php'; ?>
    <title>Crear Asignatura</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body id="body-crear-asignatura" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-crear-asignatura">
            <form id="form-crear-asignatura">
                <h1>Crear asignatura</h1>

                <label for="nombre">Nombre</label>
                <input id="nombre" type="text" placeholder="nombre"></label>

                <label for="select-profesores">Profesor</label>
                <select id="select-profesores" name="id_profesores[]"></select>

                <label for="select-cursos">Curso</label>
                <select id="select-cursos" name="id_cursos[]"></select>

                <input type="submit" value="Crear">
            </form>
        </main>
    </div>

    <script src="../scripts/asignaturas_crear.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>