<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos</title>
    <link rel="stylesheet" href="../../styles/styles.css">
</head>

<body>

    <h2>Grupos</h2>
    <br>

    <div class="schedule-edit-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Adscripta asignada</th>
                    <th>Curso</th>
                    <th>Botones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>3ro MD</td>
                    <td>Zoe Salvatierra</td>
                    <td>Informática Bilingüe</td>
                    <td>
                        <div class="botones-edit-delete">
                            <a href="editar.php">editar</a>
                            <a href="#">X</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>3ro MR</td>
                    <td>Alma Sanchez</td>
                    <td>Informática</td>
                    <td>
                        <div class="botones-edit-delete">
                        <a href="editar.php">editar</a>
                        <a href="#">X</a>
                    </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="button-group">
        <a href="../menuRecursos.php" class="links-moverse">Volver al menú</a>
        <a href="registrar.php" class="links-moverse">Nuevo registro</a>
    </div>

</body>

</html>
