<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parte Diario</title>
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

        .accion {
            width: 30rem;
        }

        .botones-edit-delete {
            display: flex;
            justify-content: space-around;
        }

        a {
            text-decoration: none;
        }

        a[href = "#"]{
            background-color: red;
            color: black;
            font-weight: bold;
            padding: 2px;
        }
    </style>

    <h2>Registros del parte diario</h2>
    <br>
    <table>
        <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th class="accion">Accion</th>
            <th>Adscripta</th>
            <th>Botones</th>
        </tr>
        <tr>
            <td>2025-07-31</td>
            <td>09:30</td>
            <td>El estudiante Thiago Diaz se retiró por dolor de cabeza</td>
            <td>Susana Arbelo</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>2025-07-31</td>
            <td>09:30</td>
            <td>El estudiante Thiago Diaz se retiró por dolor de cabeza</td>
            <td>Susana Arbelo</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>2025-07-31</td>
            <td>09:30</td>
            <td>El estudiante Thiago Diaz se retiró por dolor de cabeza</td>
            <td>Susana Arbelo</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
    </table>
    <br>
    <a href="registrar.php">Nuevo registro</a>
</body>

</html>