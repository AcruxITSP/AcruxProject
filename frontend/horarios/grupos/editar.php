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
        <main class="main-formulario-basico">
            <form class="formulario-basico">
                <h1>Agregar Hora</h1>

                <div name="periodo-inicio-container"></div>

                <label for="materia">Materia</label>
                <select name="materia"></select>

                <label for="profesor">Profesor</label>
                <select name="profesor"></select>

                <label for="espacio">Espacio</label>
                <select name="espacio"></select>

                <input type="submit" value="Agregar">
            </form>
        </main>
    </div>

    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>