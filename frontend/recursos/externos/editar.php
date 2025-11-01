<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Document</title>
</head>

<body id="body-recurso-externo-editar" class="menues-incluidos">
    <div class="menues">
        <?php include_once '../../includes/blockSidebarMenu.php' ?>
        <?php include_once '../../includes/blockTopHeader.php' ?>
    </div>

    <div class="main-content">
        <main class="main-formulario-basico">
            <form class="formulario-basico">
                <h1>Editar Recurso Externo</h1>

                <label for="imagen">Imágen</label>
                <input type="file" name="imagen" id="imagen">

                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo"></select>

                <label for="espacio">Espacio</label>
                <select name="espacio" id="espacio">
                    <option value="0">No se especificó el espacio.</option>
                </select>

                <label for="number">Cantidad</label>
                <input type="number" name="cantidad" min="0" max="500" id="number"></input>

                <input type="submit" value="Registrar Recurso">
            </form>
        </main>
    </div>

    <script src="../../scripts/recursos_externos_editar.js"></script>
    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>