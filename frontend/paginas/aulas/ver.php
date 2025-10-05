<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver aulas</title>
</head>
<body>
    <style>
        td {
            padding: 0.2rem 1rem 0.2rem 1rem;
        }
    </style>

    <h2>Aulas</h2>

    <template id="tpl">
        <table>
            <tr>
                <th>Codigo</th>
                <th>Piso</th>
                <th>Propósito</th>
                <th>Capacidad</th>
            </tr>
        </table>
    </template>

    <div id="tablaAulas"></div>

    <p id="errorMsg"></p>
    <a href="registrar.php">Registrar aulas</a><br><br>

    <a href="../menuRecursos.php">Volver al menu</a>

    <script type="module" src="../../scripts/paginaAulas.js"></script>
</body>
</html>