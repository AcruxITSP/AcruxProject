<?php include '../util/sesiones.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../styles/styles.css">

  <!-- Keep Bootstrap icons (used in both) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Keep Font Awesome too, in case main uses it -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Keep SweetAlert2 from alejo-dev -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <title>Salones ITSP</title>
</head>

<body id="body-ver-salones" class="menues-incluidos">

  <!-- Keep PHP includes from main -->
  <div id="menues">
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
    <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
  </div>

  <div id="main-content">
    <main id="main-ver-salones">

      <!-- Keep button and dynamic templates from alejo-dev -->
      <?php if (esAdscripto()): ?>
        <button onclick="location.href='crear.php'">Crear Espacio</button>
      <?php endif; ?>

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
            <p class="capacidad" name="capacidad">Capacidad: <span>20</span></p>
            <p class="ubicacion" name="ubicacion">Ubicación: <span>Planta Baja</span></p>
            <p class="estado" name="estado">Ocupado por <span>Prof. Bruno</span></p>
          </div>
          <div class="acciones">
            <?php if (esAdscripto()): ?>
              <button class="borrar" name="borrar"><i class="bi bi-trash-fill"></i></button>
              <button class="editar" name="editar"><i class="bi bi-pencil"></i></button>
            <?php endif; ?>
          </div>
        </div>
      </template>

    </main>
  </div>

  <!-- Keep both sets of scripts -->
  <script src="../scripts/espacios_ver.js"></script>
  <script src="../scripts/menuHamburgesa.js"></script>
  <script src="../scripts/dropdownMenu.js"></script>

</body>

</html>