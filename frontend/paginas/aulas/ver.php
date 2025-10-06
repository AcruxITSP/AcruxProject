<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver aulas</title>
</head>

<body>
    <style>
        /* Nota: Si no hay registros, se mostrara un mensaje de error */

        td {
            padding: 0.2rem 1rem 0.2rem 1rem;
            width: 5rem;
            text-align: center;
        }

        .botones-edit-delete {
            display: flex;
            justify-content: space-around;
        }

        a {
            text-decoration: none;
        }

        a[href="#"] {
            background-color: red;
            color: black;
            font-weight: bold;
            padding: 2px;
        }
    </style>


    <h2>Aulas</h2>
    <br>
    <table>
        <tr>
            <th>Codigo</th>
            <th>Piso</th>
            <th>Propósito</th>
            <th>Capacidad</th>
            <th>Botones</th>
        </tr>
        <tr>
            <td>2B</td>
            <td>PB</td>
            <td>Informatica</td>
            <td>25</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>1A</td>
            <td>1</td>
            <td>Lab. Fisica</td>
            <td>20</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>3D</td>
            <td>2</td>
            <td>General</td>
            <td>40</td>
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