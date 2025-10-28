<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Cursos ITSP</title>
</head>

<body id="body-ver-cursos">
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>

    <main id="main-ver-cursos">
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
        
        <h1>Educación media tecnológica</h1>
        <div class="div-cursos-educacion-media">

            <div class="curso">
                <div class="desc">
                    <p class="nombre-curso">Tecnologías de la Información</p>
                </div>
                <ul class="curso-materias">
                    <li>Matemática</li>
                    <li>Programación</li>
                    <li>Bases de Datos</li>
                    <li>Redes</li>
                    <li>Inglés Técnico</li>
                </ul>
                <div class="botones">
                    <button class="borrar"><i class="bi bi-trash-fill"></i></button>
                    <button class="editar"><i class="bi bi-pencil"></i></button>
                </div>
            </div>

            <div class="curso">
                <div class="desc">
                    <p>Tecnologías de la Información - Bilingüe</p>
                </div>
                <ul>
                    <li>Matemática</li>
                    <li>Programación</li>
                    <li>Bases de Datos</li>
                    <li>Logíca</li>
                    <li>Inglés Técnico</li>
                </ul>
                <div class="botones">
                    <button class="borrar"><i class="bi bi-trash-fill"></i></button>
                    <button class="editar"><i class="bi bi-pencil"></i></button>
                </div>
            </div>

            <div class="curso">
                <div class="desc">
                    <p>Robotica</p>
                </div>
                <ul>
                    <li>Física</li>
                    <li>Electronica</li>
                    <li>Matemática</li>
                    <li>Fisíca</li>
                    <li>Ingles</li>
                </ul>
                <div class="botones">
                    <button class="borrar"><i class="bi bi-trash-fill"></i></button>
                    <button class="editar"><i class="bi bi-pencil"></i></button>
                </div>
            </div>
        </div>

        <h1>Cursos Terciarios</h1>
        <div class="div-cursos-terciarios">

            <div class="curso">
                <div class="desc">
                    <p>Secretariado Billingüe - Inglés</p>
                </div>
                <ul>
                    <li>Inglés</li>
                    <li>Inglés</li>
                    <li>Inglés</li>
                    <li>Inglés</li>
                    <li>Inglés</li>

                </ul>
                <div class="botones">
                    <button class="borrar"><i class="bi bi-trash-fill"></i></button>
                    <button class="editar"><i class="bi bi-pencil"></i></button>
                </div>
            </div>

            <div class="curso">
                <div class="desc">
                    <p>Tecnologo</p>
                </div>
                <ul>
                    <li>Programación l</li>
                    <li>Programación ll</li>
                    <li>Base de datos l </li>
                    <li>Inglés </li>
                </ul>
                <div class="botones">
                    <button class="borrar"><i class="bi bi-trash-fill"></i></button>
                    <button class="editar"><i class="bi bi-pencil"></i></button>
                </div>
            </div>
            <div class="curso">
                <div class="desc">
                    <p>Diseño gráfico en comunicación visual</p>
                </div>
                <ul>
                    <li>Dibujo</li>
                    <li>Dibujo tecnico l</li>
                    <li>Informatica</li>
                </ul>
                <div class="botones">
                    <button class="borrar"><i class="bi bi-trash-fill"></i></button>
                    <button class="editar"><i class="bi bi-pencil"></i></button>
                </div>
            </div>
        </div>

        <button onclick="location.href='crear.php'">Crear Curso</button>
        <button id="crear-con-template">Crear targeta <br> (Template) </button>
    </main>

    <template id="tpl-targeta-curso">
        <div class="curso">
            <div class="desc">
                <p class="nombre-curso"></p>
            </div>
            <ul class="curso-materias">
            </ul>
            <div class="botones">
                <button class="borrar"><i class="bi bi-trash-fill"></i></button>
                <button class="editar"><i class="bi bi-pencil"></i></button>
            </div>
        </div>
    </template>

    <script src="../scripts/crear_targeta_curso.js"></script>
</body>

</html>