<?php include '../util/sesiones.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Grupos</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php require dirname(__FILE__) . '/../includes/header.php' ?>
</head>

<body id="body-horarios-grupos" class="menues-incluidos">
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
    <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>

    <main id="main-horarios-grupos">
        <select id="select-grupo"></select>

        <div id="div-dias-semana">
            <button id="button-lunes">Lunes</button>
            <button id="button-martes">Martes</button>
            <button id="button-miercoles">Miércoles</button>
            <button id="button-jueves">Jueves</button>
            <button id="button-viernes">Viernes</button>
        </div>

        <div id="tabla-horas"></div>

        <?php if (esAdscripto()): ?> <button id="button-borrar-hora">-</button> <?php endif; ?>
        <?php if (esAdscripto()): ?> <button id="button-agregar-hora">+</button> <?php endif; ?>

        <!-- Modal -->
        <div id="modal-agregar-hora">
            <form id="form-agregar-hora">
                <p>Agregar Hora</p>
                <input type="button" id="modal-agregar-hora-cerrar" value="X">

                <div name="periodo-inicio-container"></div>

                <label for="materia">Materia</label>
                <select name="materia"></select>

                <label for="profesor">Profesor</label>
                <select name="profesor"></select>

                <label for="espacio">Espacio</label>
                <select name="espacio"></select>

                <p id="modal-error-profe-ocupado">El profesor se encuentra ocupado en este horario.</p>
                <p id="modal-error-espacio-ocupado">El espacio se encuentra ocupado en este horario.</p>

                <input type="submit" value="Agregar">
            </form>
        </div>
    </main>

    <!-- Template de hora -->
    <template id="template-hora">
        <div name="div-hora">
            <div name="div-hora-inicio-final">
                <p name="hora-inicio">Inicio</p>
                <p name="hora-final">Final</p>
            </div>
            <div name="div-materia">
                <p name="nombre-materia">Materia</p>
            </div>
            <div name="div-profesor">
                <p name="nombre-profesor">Profesor</p>
            </div>
            <div name="div-espacio">
                <p name="nombre-espacio">Espacio</p>
            </div>
            <div name="div-botones">
                <?php if (esAdscripto()): ?> <button name="button-editar"><i class="bi bi-pencil"></i></button> <?php endif ?>
            </div>
        </div>
    </template>

    <script src="../scripts/horarios_grupos.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>