<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Salones ITSP</title>
</head>

<body id="body-ver-salones" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-ver-salones">
            <h1>Salones</h1>
            <div class="contenedor-salones">

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Salón 1</p>
                        <p class="estado ocupado">Ocupado por <span>Prof. Bruno</span></p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Salón 2</p>
                        <p class="estado libre">Libre</p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Salón 3</p>
                        <p class="estado ocupado">Ocupado por <span>Prof. Martínez</span></p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Salón 4</p>
                        <p class="estado ocupado">Ocupado por <span>Prof. González</span></p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Salón 5</p>
                        <p class="estado ocupado">Ocupado por <span>Prof. Fernandez</span></p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>
            </div>

            <h1>Aulas</h1>
            <div class="contenedor-aulas">

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Aula 1</p>
                        <p class="estado libre">Libre</p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Aula 2</p>
                        <p class="estado ocupado">Ocupado por <span>Prof. Povea</span></p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Aula 3</p>
                        <p class="estado ocupado">Ocupado por <span>Prof. Rubil</span></p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>
            </div>

            <h1>Laboratorios</h1>
            <div class="contenedor-laboratorios">

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Laboratorio Electricidad</p>
                        <p class="estado libre">Libre</p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Laboratorio de Mantenimiento</p>
                        <p class="estado ocupado">Ocupado por <span>Prof. Galmarini</span></p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>

                <div class="tarjeta">
                    <div class="info">
                        <p class="nombre">Laboratorio Robótica</p>
                        <p class="estado ocupado">Ocupado por <span>Prof. Rubil</span></p>
                    </div>
                    <div class="acciones">
                        <a class="borrar"><i class="bi bi-trash-fill"></i></a>
                        <a class="editar" href="editar.php"><i class="bi bi-pencil"></i></a>
                        <a class="reservar" href="reservar.php">Reservar</a>
                    </div>
                </div>
            </div>

            <button onclick="location.href='crear.php'">Crear Salón</button>
        </main>

        <script src="../scripts/menuHamburgesa.js"></script>
        <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>