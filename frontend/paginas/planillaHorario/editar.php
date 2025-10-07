<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar planilla de horarios</title>
</head>

<body>
    <style>
        /* Nota: Si no hay registros, se mostrara un mensaje de error */

        td, th {
            padding: 0.4rem 1rem 0.4rem 1rem;
            border: 1px solid #4b4b4bff;
        }

        .empty {
            border: none;
        }

        tr.row-btn-eliminar td {
            text-align: center;
            border: none;
        }

        a.btn-eliminar {
            color: #f32e2eff;
            font-weight: bold;
            padding: 2px;
            font-family: 'Courier New', Courier, monospace;
        }

        a {
            text-align: center;
            text-decoration: none;
        }

    </style>

    <h2>Editar planilla de horarios actual</h2>
    <table>
        <tr class="row-btn-eliminar">
            <td class="empty"></td>
            <td><a href="#" class="btn-eliminar"> X </a></td>
            <td><a href="#" class="btn-eliminar"> X </a></td>
            <td><a href="#" class="btn-eliminar"> X </a></td>
            <td><a href="#" class="btn-eliminar"> X </a></td>
            <td><a href="#" class="btn-eliminar"> X </a></td>
        </tr>
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
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#" class="btn-eliminar"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>7:50 - 8:35</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#" class="btn-eliminar"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>8:40 - 9:25</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#" class="btn-eliminar"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>9:30 - 10:15</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#" class="btn-eliminar"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>10:20 - 11:05</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#" class="btn-eliminar"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>11:10 - 11:55</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#" class="btn-eliminar"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>12:00 - 12:45</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#" class="btn-eliminar"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>12:50 - 13:35</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#" class="btn-eliminar"> X </a>
                </div>
            </td>
        </tr>
    </table>
    <br>
    <a href="ver.php">Volver</a>
</body>

</html>