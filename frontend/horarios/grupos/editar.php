<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar hora</title>
    <link rel="stylesheet" href="../../styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php require dirname(__FILE__) . '/../../includes/header.php' ?>
</head>

<body id="body-horarios-grupos" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
         <main id="main-editar-horarios">
            <form id="form-editar-horario" class="formulario-basico">
                <h1>Editar Hora</h1>

                <label for="select-materia">Materia</label>
                <select name="select-materia" id="select-materia"></select>

                <label for="select-profesor">docente</label>
                <select name="select-profesor" id="select-profesor"></select>

                <label for="select-espacio">Espacio</label>
                <select name="select-espacio" id="select-espacio"></select>

                <input type="submit" value="Guardar">
            </form>
        </main>
    </div>

    <script src="../../scripts/horarios_grupos_editar.js"></script>
    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>