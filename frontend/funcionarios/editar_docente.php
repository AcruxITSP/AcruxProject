<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php require __DIR__ . '/../systems/inputlist/ilists.php'; ?>
    <title>Editar Docente</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body id="body-crear-docente" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-crear-docente">
            <form id="form-editar-docente" class="formulario-basico">
                <h1>Editar Docente</h1>

                <div id="grid">
                    <div id="inputs">
                        <input type="text" placeholder="nombre">
                        <input type="text" placeholder="apellido">
                        <input type="text" placeholder="contraseña">
                        <input type="text" placeholder="CI">
                        <input type="text" placeholder="email">
                    </div>

                    <div id="select-materias">
                        <label>Materias</label>
                        <input id="label-opcionesMaterias" type="text" placeholder="Seleccione las materias" readonly>
                        <div id="opcionesMaterias" class="scrollable-list"></div>
                    </div>
                </div>

                <input type="submit" value="Crear">
            </form>
        </main>
    </div>

    <script src="../scripts/editar_docente.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>