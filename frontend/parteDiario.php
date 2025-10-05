<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parte Diario</title>
    <link rel="stylesheet" href="styles/styles.css">

</head>
<body>
    <style>
        td {
            padding: 0.2rem 1rem 0.2rem 1rem;
        }
    </style>

    <h2>Parte Diario</h2>
    <template id="tpl">
        <table>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Descripcion</th>
            </tr>
        </table>
    </template>

    <div id="tablaRegistros"></div>

    <p id="errorMsg"></p>
    <a href="index.php">Volver al menu</a>

    <script type="module" src="scripts/paginaPartediario.js"></script>
</body>
</html>