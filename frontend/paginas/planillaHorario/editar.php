<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar planilla de horarios</title>
    <link rel="stylesheet" href="../../styles/styles.css">
    <!-- Font Awesome para iconos -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="schedule-edit-container">
        <h2>Editar planilla de horarios actual</h2>
        <table>
            <br>
        <tr>
            <th class="empty"></th>
            <th>Lunes</th>
            <th>Martes</th>
            <th>Miércoles</th>
            <th>Jueves</th>
            <th>Viernes</th>
            <th class="acciones">Acciones</th> <!-- se agrega encabezado para la columna final -->
        </tr>

            <!-- Filas de horario -->
            <tr>
                <td>7:00 - 7:45</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-eliminar btn-link">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>7:50 - 8:35</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-eliminar btn-link">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>8:40 - 9:25</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-eliminar btn-link">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>9:30 - 10:15</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-eliminar btn-link">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>10:20 - 11:05</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-eliminar btn-link">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>11:10 - 11:55</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-eliminar btn-link">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>12:00 - 12:45</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td>
                    <div class="button-group">
                        <a href="editar.php" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-eliminar btn-link">X</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>12:50 - 13:35</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td>
                    <div class="button-group">
                        <a href="#" class="btn-link btn-editar">
                            <img src="../../img/pencil.svg" alt="Editar" class="icon-btn">
                        </a>
                        <a href="#" class="btn-link btn-eliminar">X</a>
                    </div>
                </td>
            </tr>
        </table>

        <a href="ver.php" class="volver">
            <img src="../../img/arrow-left.svg" alt="Volver" style="width:20px; height:20px; margin-right:8px;">
            Volver
        </a>
    </div>
</body>
</html>
