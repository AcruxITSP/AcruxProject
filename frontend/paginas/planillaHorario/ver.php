<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver planilla de horarios</title>
</head>
<body>
    <style>
        /* Nota: Si no hay registros, se mostrara un mensaje de error */

        td, th {
            padding: 0.4rem 1rem 0.4rem 1rem;
            border: 1px solid #4b4b4bff;
        }



        .empty {
            border: 1px solid lightgray;
        }

    </style>

    <h2>Planilla de horarios</h2>

    <template id="tpl">
        <table>
            <tr>
                <th class="empty"></th>
            </tr>
        </table>
    </template>

    <div id="tablaHorario"></div>

    <p id="errorMsg"></p>
    <a href="registrar.php">Crear una planilla</a><br><br>

    <a href="../menuRecursos.php">Volver al menu</a>

    <script type="module" src="../../scripts/paginaPlanillaHorario.js"></script>
</body>
</html>