<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Definir horas</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body id="body-horas" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <div id="main-horas" class="main-formulario-basico">
            <div id="lista-intervalos" class="formulario-basico">
                <div id="scrollable-list-intervalos" class="scrollable-list">
                    <template id="tpl-targeta-intervalo">
                        <div class="targeta-intervalo">
                            <p></p>
                        </div>
                    </template>
                    <div class="targeta-intervalo">
                        <p>7:00 - 7:45</p>
                    </div>
                    <div class="targeta-intervalo">
                        <p>12:00 - 12:45</p>
                    </div>
                    <div class="targeta-intervalo">
                        <p>12:00 - 12:45</p>
                    </div>
                    <div class="targeta-intervalo">
                        <p>12:00 - 12:45</p>
                    </div>
                    <div class="targeta-intervalo">
                        <p>12:00 - 12:45</p>
                    </div>
                    <div class="targeta-intervalo">
                        <p>12:00 - 12:45</p>
                    </div>
                    <div class="targeta-intervalo">
                        <p>12:00 - 12:45</p>
                    </div>
                    <div class="targeta-intervalo">
                        <p>12:00 - 12:45</p>
                    </div>
                    <div class="targeta-intervalo">
                        <p>12:00 - 12:45</p>
                    </div>
                </div>
                <div>
                    <button id="btn-add-hora">+</button>
                </div>
            </div>
            <form class="formulario-basico" id="formulario-registrar-horario">
                <h1>Registrar</h1>

                <label for="input-hora-inicio">Hora Inicio: <input type="time" id="input-hora-inicio"></label>
                <label for="input-hora-final">Hora Final: <input type="time" id="input-hora-final"></label>
                <input type="number" placeholder="Duración de las clases (minutos)" id="duracion-clase">
                <input type="number" placeholder="Duración de los descansos (minutos)" id="duracion-descanso">

                <button id="btn-registrar">Registrar</button>
            </form>
        </div>
    </div>

    <script src="../scripts/horarios_horas.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>