<?php
include '../../util/sesiones.php';
if (!estaLogeado()) {
    header("Location: ../../cuenta/login.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php require __DIR__ . '/../../systems/inputlist/ilists.php'; ?>
    <link rel="stylesheet" href="../../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Editar Recursos Internos</title>
</head>

<body id="body-recurso-interno-editar" class="menues-incluidos">
    <div id="menues">
        <?php include_once '../../includes/blockSidebarMenu.php' ?>
        <?php include_once '../../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main class="main-formulario-basico">
            <form id="form-editar-recurso-interno" class="formulario-basico">
                <h1>Editar Recurso Interno</h1>

                <label for="tipo">Tipo</label>
                <input name="tipo" id="tipo" required>

                <label>Espacios</label>
                <div id="ilist-espacios-y-cantidades" ilist-suplier="IListEspacioCantidadSuplier" ilist-title="" ilist-inames=""></div>

                <input type="submit" value="Guardar">
            </form>
        </main>
    </div>

    <script src="../../inputs.js"></script>
    <script src="../../scripts/recursos_internos_editar.js"></script>
    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>