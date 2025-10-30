<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Salones ITSP</title>
</head>
<body id="body-ver-salones">
    <main id="main-ver-salones">
        <button onclick="location.href='crear.php'">Crear Salón</button>
    </main>

    <template id="template-div-por-tipo">
        <div>
            <h1 name="tipo">Salones</h1>
            <div class="contenedor-salones" name="contenedor-salones"></div>
        </div>
    </template>

    <template id="template-tarjeta">
        <div class="tarjeta">
            <div class="info">
                <p class="nombre" name="nombre">Salón 1</p>
                <p class="estado ocupado" name="estado">Ocupado por <span>Prof. Bruno</span></p>
            </div>
            <div class="acciones">
                <button class="borrar" name="borrar"><i class="bi bi-trash-fill"></i></button>
                <button class="editar" name="editar"><i class="bi bi-pencil"></i></button>
            </div>
        </div>
    </template>
    <script src="../scripts/espacios_ver.js"></script>
</body>
</html>
