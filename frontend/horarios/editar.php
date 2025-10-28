<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php require __DIR__ . '/../systems/inputlist/ilists.php'; ?>
    <title>Document</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body id="body-editar-horarios">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-editar-horarios">
            <form id="form-editar">
                <h1>Editar Hora</h1>

                <label for="materia">Materia</label>
                <select name="materia" id="materia">
                    <option>Programacion</option>
                    <option>Ciberseguridad</option>
                    <option>Emprendedurismo</option>
                </select>

                <label for="docente">Docente</label>
                <select name="docente" id="docente">
                    <option>Hernesto Rodriguez</option>
                    <option>Pablo Sanchez</option>
                    <option>Walter White</option>
                </select>

                <label for="espacio">Espacio</label>
                <select name="espacio" id="espacio">
                    <option>Salon 1</option>
                    <option>Lab. Quimica</option>
                    <option>Salon 3</option>
                </select>

                <div class="div-vacio"> Div vacio</div>

                <input type="submit" value="Guardar">
            </form>
        </main>
    </div>

    <script src="../inputs.js"></script>
    <script src="../scripts/recursos_crear.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>