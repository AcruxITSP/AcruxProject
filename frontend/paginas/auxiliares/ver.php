<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auxiliares</title>
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
        }

        a[href = "#"]{
            background-color: red;
            color: black;
            font-weight: bold;
            padding: 2px;
        }
    </style>

    <h2>Auxiliares</h2>
    <br>
    <table>
        <tr>
            <th>DNI</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Botones</th>
        </tr>
        <tr>
            <td>56473235</td>
            <td>Susana</td>
            <td>Arbelo</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>56473235</td>
            <td>Federico</td>
            <td>Fagundez</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>56473235</td>
            <td>Facundo</td>
            <td>Rubil</td>
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