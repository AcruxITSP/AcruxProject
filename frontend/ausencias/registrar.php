<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ausencias</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-registrar-ausencia">
            <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>

            <div id="modal-registrar-ausencia">
                <form>
                    <h2>Registrar Ausencia</h2>
                    <label for="fechaInicio">Inicio <input type="date" id="fechaInicio"></label>
                    <label for="fechaFin">Fin <input type="date" id="fechaFin"></label>

                    <p>Horas</p>
                    <label for="allDay"><input type="checkbox" id="allDay"> Todo el día</label>

                    <div id="lista-intervalos">
                        Aca van los intervalos
                    </div>

                    <p>Motivo</p>
                    <textarea id="input-motivo"></textarea>

                    <input type="submit" value="Registrar">
                </form>
            </div>
        </main>
    </div>

    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>