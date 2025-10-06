<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver aulas</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <table id="table-registros">
        <tr>
            <th>Codigo</th>
            <th>Piso</th>
            <th>Propósito</th>
            <th>Capacidad</th>
            <th>Botones</th>
        </tr>
    </table>

    <template id="template-registro">
        <tr>
            <td name="codigo">2B</td>
            <td name="piso">PB</td>
            <td name="proposito">Informatica</td>
            <td name="capacidad">25</td>
            <td>
                <div class="botones-edit-delete">
                    <a name="editar" href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
    </template>

    <br>
    <a href="registrar.php">Nuevo registro</a>

    <script src="../../scripts/cliente.js"></script>
    <script src="ver.js"></script>
</body>

</html>