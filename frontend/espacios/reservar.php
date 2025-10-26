<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Document</title>
</head>
<body id="body-espacio-reservar">
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
            <label class="label-espacio-reservar" name="label"><input type="checkbox" name="horas[]" value="0" disabled="false"><p name="text"></p></label>
        </template>
    </main>

    <script src="../scripts/espacios_reservar.js"></script>
</body>
</html>