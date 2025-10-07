<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas de salones</title>
    <link rel="stylesheet" href="../../styles/styles.css">

</head>

<body>
    <style>
        /* Nota: Si no hay registros, se mostrara un mensaje de error */

        td {
            padding: 0.2rem 1rem 0.2rem 1rem;
            width: 10rem;
            text-align: center;
        }

        .botones-edit-delete {
            display: flex;
            justify-content: space-around;
        }

        a {
            text-decoration: none;
            margin-right: 4rem;
        }

        a[href = "#"]{
            background-color: red;
            color: black;
            font-weight: bold;
            padding: 2px;
        }

        th.reservador {
            width: 20%;
        }
    </style>

    <h2>Reservas de salones</h2>
    <br>
    <table>
        <tr>
            <th>Aula</th>
            <th class="reservador">Reservador</th>
            <th>Dia</th>
            <th>Desde</th>
            <th>Hasta</th>
            <th>Botones</th>
        </tr>
        <tr>
            <td>3B</td>
            <td>Jhon Doe</td>
            <td>03/07/2025</td>
            <td>12:00</td>
            <td>12:45</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>2C</td>
            <td>Pablo Hernandez</td>
            <td>06/08/2025</td>
            <td>7:50</td>
            <td>8:35</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>1D</td>
            <td>Roberto Salvatierra</td>
            <td>10/5/2025</td>
            <td>7:00</td>
            <td>10:15</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
    </table>
    <br>
    <a href="registrar.php" class="links-moverse">Hacer una reserva</a>
    <a href="../aulas/ver.php" class="links-moverse">Volver</a>
</body>

</html>