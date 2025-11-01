<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Document</title>
</head>

<body id="body-espacio-reservar" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <main id="main-espacio-reservar">
            <h1>Reservar Espacio</h1>
            <div id="top">
                <img id="imagen">
                <p id="espacio"></p>
            </div>
            <form id="form-reservar-espacio">
                <div id="checkboxes-intervalos">

                </div>
                <input type="submit">
            </form>

            <template id="template-checkbox-intervalo">
                <label class="label-espacio-reservar" name="label"><input type="checkbox" name="horas[]" value="0" disabled="false">
                    <p name="text"></p>
                </label>
            </template>
        </main>
    </div>

    <script src="../scripts/espacios_reservar.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>