<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver aulas</title>
    <link rel="stylesheet" href="../../styles/styles.css">
</head>

<body>
    <div class="schedule-container">
        <h2>Aulas</h2>
        <br>
        <table class="schedule-table">
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
                <td>Informática</td>
                <td>25</td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-link btn-eliminar">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>1A</td>
                <td>1</td>
                <td>Lab. Física</td>
                <td>20</td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-link btn-eliminar">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>3D</td>
                <td>2</td>
                <td>General</td>
                <td>40</td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-link btn-eliminar">X</a>
                    </div>
                </td>
            </tr>
        </table>

        <div class="button-group">
            <a href="../menuRecursos.php" class="btn-link">Volver al menú</a>
            <a href="registrar.php" class="btn-link">Nuevo registro</a>
            <a href="../reservas/ver.php" class="btn-link">Ver reservas</a>
        </div>
    </div>
</body>

</html>
