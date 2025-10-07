<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar reservas</title>
    <link rel="stylesheet" href="../../styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <form id="reserva-registrar-form">
        <label for="id_aula">Aula</label>
        <select id="id_aula" name="id_aula"></select>

        <label for="id_hora_inicio">Hora Inicio</label>
        <select id="id_hora_inicio" name="id_hora_inicio"></select>

        <label for="id_hora_final">Hora Final</label>
        <select id="id_hora_final" name="id_hora_final"></select>

        <input type="submit" value="Registrar">
    </form>

    <template id="template_option">
        <option value=""></option>
    </template>

    <br>
    <a href="../menurecursos.php">Volver</a>

    <script src="../../scripts/cliente.js"></script>
    <script src="registrar.js"></script>
</body>

</html>