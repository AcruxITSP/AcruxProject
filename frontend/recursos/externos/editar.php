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
    <?php include_once '../../includes/blockSidebarMenu.php' ?>
    <?php include_once '../../includes/blockTopHeader.php' ?>

    <main>
        <form id="form">
            <h1>Editar Recurso Externo</h1>
            <label for="imagen">Imagen</label>
            <input type="file" name="imagen">

            <label for="tipo">Tipo</label>
            <select name="tipo"></select>

            <label for="espacio">Espacio</label>
            <select name="espacio">
                <option value="0">No es especifico al espacio.</option>
            </select>

            <label for="number">Cantidad</label>
            <input type="number" name="cantidad" min="0" max="500"></input>

            <input type="submit" value="Registrar Recurso">
        </form>
    </main>

    <script src="../../scripts/recursos_externos_editar.js"></script>
    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>