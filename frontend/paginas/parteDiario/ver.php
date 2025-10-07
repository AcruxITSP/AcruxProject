<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parte Diario</title>
    <link rel="stylesheet" href="../../styles/styles.css">

</head>

<body id="ParteDiario-body">
    <div class="Main-ParteDiario">
        <h2 id="Titulo-ParteDiario">Registros del parte diario</h2>
        <br>
        <table class="Tabla-ParteDiario">
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
                <td>Zoe Salvatierra</td>
                <td>
                    <div class="botones-edit-delete">
                        <a href="editar.php"> editar </a>
                        <a href="#"> X </a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>2025-05-20</td>
                <td>12:10</td>
                <td>La estudiante Olivia Gómez se retiró. Su madre la vino a buscar</td>
                <td>Zoe Salvatierra</td>
                <td>
                    <div class="botones-edit-delete">
                        <a href="editar.php"> editar </a>
                        <a href="#"> X </a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>2025-09-04</td>
                <td>14:30</td>
                <td>El estudiante Felipe Mendez</td>
                <td>Zoe Salvatierra</td>
                <td>
                    <div class="botones-edit-delete">
                        <a href="editar.php"> editar </a>
                        <a href="#"> X </a>
                    </div>
                </td>
            </tr>
        </table>
        <br>
        <div>
            <a href="registrar.php" class="links-moverse">Nuevo registro</a>
            <a href="../menuRecursos.php" class="links-moverse">Volver</a>
        </div>
    </div>
</body>

</html>