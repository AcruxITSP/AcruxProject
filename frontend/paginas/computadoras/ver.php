<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computadoras</title>
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

    <h2>Computadoras</h2>
    <br>
    <table>
        <tr>
            <th>SO</th>
            <th>Estado</th>
            <th>Problema</th>
            <th>Id Aula</th>
        </tr>
        <tr>
            <td>Windows</td>
            <td>OK</td>
            <td>Ninguno</td>
            <td>1</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>Windows</td>
            <td>OK</td>
            <td>Ninguno</td>
            <td>1</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>Windows</td>
            <td>Malfuncionamiento</td>
            <td>La pantalla no puede mostrar el color azul</td>
            <td>1</td>
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