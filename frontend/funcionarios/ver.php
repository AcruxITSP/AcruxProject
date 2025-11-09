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
        <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario" name="nombre-funcionario">Facundo Rubil</p>
            <p class="cargo" name="cargo">Docente</p>
          </div>
          <ul class="info-funcionario">
            <li class="CI" name="CI">Cédula: 11111111</li>
            <li class="EMail" name="EMail">Correo: facundo.rubil@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <a><button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button></a>
            <a href="editar.php"><button class="editar" title="Editar"><i class="bi bi-pencil"></i></button></a>
          </div>
        </div>

        <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario" name="nombre-funcionario">Franco Povea</p>
            <p class="cargo" name="cargo">Docente</p>
          </div>
          <ul class="info-funcionario">
            <li class="CI" name="CI">Cédula: 22222222</li>
            <li class="EMail" name="EMail">Correo: franco.povea@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <a><button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button></a>
            <a href="editar.php"><button class="editar" title="Editar"><i class="bi bi-pencil"></i></button></a>
          </div>
        </div>

        <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario" name="nombre-funcionario">Lucía Fernández</p>
            <p class="cargo" name="cargo">Personal de Limpieza</p>
          </div>
          <ul class="info-funcionario">
            <li class="CI" name="CI">Cédula: 33333333</li>
            <li class="EMail" name="EMail">Correo: lucia.fernandez@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <a><button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button></a>
            <a href="editar.php"><button class="editar" title="Editar"><i class="bi bi-pencil"></i></button></a>
          </div>
        </div>

        <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario" name="nombre-funcionario">Carlos Gómez</p>
            <p class="cargo" name="cargo">Personal de Limpieza</p>
          </div>
          <ul class="info-funcionario">
            <li class="CI" name="CI">Cédula: 44444444</li>
            <li class="EMail" name="EMail">Correo: carlos.gomez@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <a><button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button></a>
            <a href="editar.php"><button class="editar" title="Editar"><i class="bi bi-pencil"></i></button></a>
          </div>
        </div>
      </div>

      <!-- BOTONES FLOTANTES -->
      <div class="botones-flotantes">
        <button onclick="location.href='crear_docente.php'"><i class="bi bi-person-plus"></i> Crear funcionario</button>
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
          <ul class="info-funcionario">
            <li class="CI" name="CI">Cédula: 44444444</li>
            <li class="EMail" name="EMail">Correo: carlos.gomez@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <a><button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button></a>
            <a href="editar.php"><button class="editar" title="Editar"><i class="bi bi-pencil"></i></button></a>
          </div>
        </div>
      </div>
  </template>

  <script src="../scripts/funcionarios_ver.js"></script>
  <script src="../scripts/menuHamburgesa.js"></script>
  <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>