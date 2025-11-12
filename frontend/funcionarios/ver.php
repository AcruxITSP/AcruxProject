<?php include '../util/sesiones.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../styles/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>


<body id="body-ver-funcionarios" class="menues-incluidos">
  <div id="menues">
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
    <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
  </div>

  <div id="main-content">
    <main>
      <header class="page-header">
        <h1>Funcionarios ITSP</h1>
      </header>

      <div class="contenedor-funcionarios">
      </div>

      <!-- BOTONES FLOTANTES -->
      <div class="botones-flotantes">
        <?php if(esAdscripto()): ?>
        <button onclick="location.href='crear_docente.php'"><i class="bi bi-person-plus"></i> Crear funcionario</button>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <button class="btn-volver" onclick="history.back()" title="Volver">
    <i class="bi bi-arrow-left"></i>
  </button>

  <template id="tpl-tarjeta-funcionario">
    <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario" name="nombre-funcionario">Carlos Gómez</p>
            <p class="cargo" name="cargo">Personal de Limpieza</p>
          </div>
          <?php if(estaLogeado()): ?>
            <ul class="info-funcionario">
              <li class="CI" name="CI">Cédula: 44444444</li>
              <li class="EMail" name="EMail">Correo: carlos.gomez@itsp.edu.uy</li>
            </ul>
          <?php endif; ?>
          <div class="botones">
            <?php if(esAdscripto()): ?>
              <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
              <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
            <?php endif; ?>
          </div>
        </div>
      </div>
  </template>

  <script src="../scripts/funcionarios_ver.js"></script>
  <script src="../scripts/menuHamburgesa.js"></script>
  <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>