<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rgistrar Ausencias</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body>
    <main>
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
</body>

</html>