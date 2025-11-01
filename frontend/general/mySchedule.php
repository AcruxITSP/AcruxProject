<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planilla de Horarios</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body id="body-my-schedule" class="menues-incluidos">
    <div class="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div class="main-content">
        <header class="header">
            <h1 class="titulo">Planilla de Horarios</h1>
        </header>

        <main id="main-my-schedule">
            <section class="contenedor-planilla">
                <table class="tabla-horarios" cellspacing="0" cellpadding="0">
                    <thead class="tabla-head">
                        <tr class="fila-head">
                            <th class="columna-hora">Hora</th>
                            <th>Espacio</th>
                            <th>Grupo</th>
                        </tr>
                    </thead>

                    <tbody class="tabla-body">

                        <tr class="fila">
                            <td class="hora">07:00 - 07:45</td>
                            <td class="espacio">Salon 4</td>
                            <td class="grupo">3°MD</td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">07:50 - 08:35</td>
                            <td class="espacio">Salon 5</td>
                            <td class="grupo">1° MD</td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">08:40 - 09:25</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">09:30 - 10:15</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">10:20 - 11:05</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">11:10 - 11:55</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">12:00 - 12:45</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">12:50 - 13:35</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">13:40 - 14:25</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">14:30 - 15:15</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                        <tr class="fila">
                            <td class="hora">15:20 - 16:05</td>
                            <td class="espacio"></td>
                            <td class="grupo"></td>
                        </tr>

                    </tbody>
                </table>

                <div class="botones">
                    <button class="btn"><b>Agregar inasistencia</b></button>

                    <button class="btn" onclick="location.href='menurecursos.php'">
                        <i class="bi bi-arrow-left"></i>
                    </button>

                    <button class="btn" id="btn-agregar-fila"><b>Agregar Fila (template)</b></button>
                </div>

            </section>
        </main>
    </div>

    <template id="tpl-fila">
        <tr class="fila">
            <td class="hora"></td>
            <td class="espacio"></td>
            <td class="grupo"></td>
        </tr>
    </template>

    <script src="../scripts/crear_fila_mySchedule.js"></script>
</body>

</html>