<?php include '../util/sesiones.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php require dirname(__FILE__).'/../includes/header.php'?>
    <title>Cursos ITSP</title>
</head>
<body id="body-ver-cursos">
    <main id="main-ver-cursos">
        <h1>Asignaturas</h1>
        <div class="div-cursos-educacion-media" id="asignatura-container">
            
        </div>

        <button onclick="location.href='crear.php'">Crear Curso</button>
        <button id="crear-con-template">Crear targeta <br> (Template) </button>
    </main>

    <template id="tpl-targeta-asignatura">
        <div class="curso">
                <div class="desc">
                    <p class="nombre-curso" name="nombre">Tecnologías de la Información</p>
                </div>
                <div class="asignatura-horizontal">
                    <div>
                        <p>Cursos</p>
                        <ul class="curso-materias" name="li-cursos">
                        </ul>
                    </div>
                    <div>
                        <p>Docentes</p>
                        <ul class="curso-materias" name="li-docentes">
                        </ul>
                    </div>
                </div>
                <div class="botones">
                    <?php if(esAdscripto()):?>
                        <button class="borrar" name="button-borrar"><i class="bi bi-trash-fill"></i></button>                    
                        <button class="editar" name="button-editar"><i class="bi bi-pencil"></i></button>
                    <?php endif; ?>
                </div>
            </div>
    </template>

    <script src="../scripts/asignaturas_ver.js"></script>
</body>
</html>
