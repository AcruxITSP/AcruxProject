<?php require __DIR__.'/../util/sesiones.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Cursos ITSP</title>
</head>

<body id="body-ver-cursos">
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>

    <main id="main-ver-cursos">
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
        
        <h1>Cursos</h1>
        <div class="div-cursos-educacion-media" id="curso-container">
        </div>

        <button onclick="location.href='crear.php'">Crear Curso</button>
    </main>

    <template id="tpl-targeta-curso">
        <div class="curso">
            <div class="desc">
                <p class="nombre-curso" name="nombre-curso"></p>
            </div>
            <ul class="curso-materias" name="materias">
            </ul>
            <div class="botones">
                <?php if(esAdscripto()): ?>
                    <button class="borrar" name="button-borrar"><i class="bi bi-trash-fill"></i></button>
                    <button class="editar" name="button-editar"><i class="bi bi-pencil"></i></button>
                <?php endif; ?>
            </div>
        </div>
    </template>

    <script src="../scripts/cursos_ver.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>