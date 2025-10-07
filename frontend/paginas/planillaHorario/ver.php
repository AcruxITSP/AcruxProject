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

        a.links-moverse {
            margin-right: 2rem;
        }

        .empty {
            border: 1px solid lightgray;
        }
    </style>

    <h2>Planilla de horarios</h2>
    <table>
        <tr>
            <th class="empty"></th>
            <th>Lunes</th>
            <th>Martes</th>
            <th>Miercoles</th>
            <th>Jueves</th>
            <th>Viernes</th>
        </tr>
        <tr>
            <td>7:00 - 7:45</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>7:50 - 8:35</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>8:40 - 9:25</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>9:30 - 10:15</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>10:20 - 11:05</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>11:10 - 11:55</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>12:00 - 12:45</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>12:50 - 13:35</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <br>
    <a href="editar.php" class="links-moverse">Editar la planilla actual</a>
    <br><br>
    <a href="registrar.php" class="links-moverse">Hacer una nueva planilla</a>
    <br><br>
    <a href="../menuRecursos.php" class="links-moverse">Volver al menú</a>
</body>

</html>