<?php
include '../util/sesiones.php';
if(!esAdscripto())
{
    header("Location: ../cuenta/login.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php require __DIR__.'/../systems/inputlist/ilists.php'; ?>
    <title>Document</title>
    <link rel="stylesheet" href="../styles/styles.css">

</head>
<body id="body-crear-recursos">
    <main id="main-crear-recursos">
        <form id="form-crear-recursos">
            <h1>Crear Recurso</h1>

            <label for="localidad">Localidad</label>
            <select name="localidad">
                <option value="externo">Externo</option>
                <option value="interno">Interno</option>
            </select>

            <label for="tipo">Tipo</label>
            <input name="tipo">

            <!-- aqui se moveran los inputs dependiendo de la localidad -->
            <div id="inputs-segun-localidad">
                
            </div>

            <input type="submit" value="Registrar Recurso">
        </form>
    </main>

    <!-- Aca se almacenas los inputs que no deben aparecer en el formulario hasta que se les indiquen-->
    <div id="inputs-apartados">
        <div id="inputs-para-externo">
            <label for="id_espacio">Espacio</label>
            <select name="id_espacio">
                <option value="0">N/A</option>
            </select>

            <label for="cantidad_total">Cantidad</label>
            <input type="number" name="cantidad_total" min="1" max="500"></input>
        </div>

        <div id="inputs-para-interno">
            <label>Espacios</label>
            <div id="ilist-espacios-y-cantidades" ilist-suplier="IListEspacioCantidadSuplier" ilist-title="" ilist-inames=""></div>
        </div>
    </div>

    <script src="../inputs.js"></script>
    <script src="../scripts/recursos_crear.js"></script>
</body>
</html>