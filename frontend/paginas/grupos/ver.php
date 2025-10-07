<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos</title>
    <link rel="stylesheet" href="../../styles/styles.css">

</head>

<body>
    <style>
        /* Nota: Si no hay registros, se mostrara un mensaje de error */

        td {
            padding: 0.2rem 1rem 0.2rem 1rem;
            width: 12rem;
            text-align: center;
        }

        .botones-edit-delete {
            display: flex;
            justify-content: space-around;
        }

        a {
            text-decoration: none;
        }

        a.links-moverse {
            margin-right: 2rem;
        }

        a[href = "#"]{
            background-color: red;
            color: black;
            font-weight: bold;
            padding: 2px;
        }
    </style>

    <h2>Grupos</h2>
    <br>
    <table>
        <tr>
            <th>Codigo</th>
            <th>Adscripta asignada</th>
            <th>Curso</th>
            <th>Botones</th>
        </tr>
        <tr>
            <td>3ro MD</td>
            <td>Zoe Salvatierra</td>
            <td>Informática Bilingüe</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
        <tr>
            <td>3do MR</td>
            <td>Alma Sanchez</td>
            <td>Informática</td>
            <td>
                <div class="botones-edit-delete">
                    <a href="editar.php"> editar </a>
                    <a href="#"> X </a>
                </div>
            </td>
        </tr>
    </table>
    <br>
    <a href="../menuRecursos.php" class="links-moverse">Volver al menú</a>
    <a href="registrar.php" class="links-moverse">Nuevo registro</a>
</body>

</html>