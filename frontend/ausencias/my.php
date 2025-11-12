<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Ausencias</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body id="body-mis-ausencias" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <h2>Mis Ausencias</h2>
        <main id="main-mis-ausencias">
            <div class="scrollable-list" id="scrollable-list-ausencias">
                <div class="targeta-ausencia">
                    <div class="container-hora-fecha">
                        <p>15/09/2015</p>
                        <hr class="vertical">
                        <p>Todo el día</p>
                    </div>
                    <hr class="horizontal">
                    <div class="motivo-container">
                        <p><strong>Motivo:</strong> Solicitó el día libre</p>
                    </div>
                </div>
                <div class="targeta-ausencia">
                    <div class="container-hora-fecha">
                        <p>17/03/2025</p>
                        <hr class="vertical">
                        <p>7:00 - 14:00</p>
                    </div>
                    <hr class="horizontal">
                    <div class="motivo-container">
                        <p><strong>Motivo:</strong> Relleno</p>
                    </div>
                </div>
                <div class="targeta-ausencia">
                    <div class="container-hora-fecha">
                        <p>10/12/2025</p>
                        <hr class="vertical">
                        <p>7:50 - 9:00</p>
                    </div>
                    <hr class="horizontal">
                    <div class="motivo-container">
                        <p><strong>Motivo:</strong> Relleno</p>
                    </div>
                </div>
                <div class="targeta-ausencia">
                    <div class="container-hora-fecha">
                        <p>10/12/2025</p>
                        <hr class="vertical">
                        <p>7:50 - 9:00</p>
                    </div>
                    <hr class="horizontal">
                    <div class="motivo-container">
                        <p><strong>Motivo:</strong> Relleno</p>
                    </div>
                </div>
                <div class="targeta-ausencia">
                    <div class="container-hora-fecha">
                        <p>10/12/2025</p>
                        <hr class="vertical">
                        <p>7:50 - 9:00</p>
                    </div>
                    <hr class="horizontal">
                    <div class="motivo-container">
                        <p><strong>Motivo:</strong> Relleno</p>
                    </div>
                </div>
                <div class="targeta-ausencia">
                    <div class="container-hora-fecha">
                        <p>10/12/2025</p>
                        <hr class="vertical">
                        <p>7:50 - 9:00</p>
                    </div>
                    <hr class="horizontal">
                    <div class="motivo-container">
                        <p><strong>Motivo:</strong> Relleno</p>
                    </div>
                </div>
                <div class="targeta-ausencia">
                    <div class="container-hora-fecha">
                        <p>10/12/2025</p>
                        <hr class="vertical">
                        <p>7:50 - 9:00</p>
                    </div>
                    <hr class="horizontal">
                    <div class="motivo-container">
                        <p><strong>Motivo:</strong> Relleno</p>
                    </div>
                </div>
                <div class="targeta-ausencia">
                    <div class="container-hora-fecha">
                        <p>10/12/2025</p>
                        <hr class="vertical">
                        <p>7:50 - 9:00</p>
                    </div>
                    <hr class="horizontal">
                    <div class="motivo-container">
                        <p><strong>Motivo:</strong> Relleno</p>
                    </div>
                </div>
            </div>
            <div id="modal-registrar-ausencia">
                <form id="form-registrar-ausencia" class="formulario-basico">
                    <h1>Registrar Ausencia</h1>
                    <label for="fechaInicio">Inicio <input type="date" id="fechaInicio" name="fechaInicio"></label>
                    <label for="fechaFin">Fin <input type="date" id="fechaFin" name="fechaFin"></label>

                    <label>Hora</label>
                    <label for="allDay" id="allday-label">Todo el día <input type="checkbox" id="allDay" name="allDay"></label>

                    <div id="select-intervalos">
                        <input id="label-opcionesIntervalos" type="text" placeholder="Seleccione los intervalos" readonly>
                        <div id="opcionesIntervalos" class="scrollable-list"></div>
                    </div>

                    <div>
                        <label for="input-motivo">Motivo</label>
                        <textarea id="input-motivo" name="motivo"></textarea>
                    </div>

                    <input type="submit" value="Registrar">
                </form>
            </div>
        </main>
        <button class="btn-volver" onclick="history.back()" title="Volver">
            <i class="bi bi-arrow-left"></i>
        </button>
    </div>

    <script src="../scripts/ausencias_registrar.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>