<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../styles/styles.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <title>Funcionarios ITSP</title>
</head>

<body id="body-ver-funcionarios">
  <div class="menues">
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
    <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
  </div>

  <div class="main-content">
    <main>
      <header class="page-header">
        <h1>Funcionarios ITSP</h1>
      </header>

      <div class="contenedor-funcionarios">
        <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario">Facundo Rubil</p>
            <p>Docente de Programación</p>
          </div>
          <ul class="info-funcionario">
            <li>Cédula: 11111111</li>
            <li>Correo: facundo.rubil@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
            <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
          </div>
        </div>

        <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario">Franco Povea</p>
            <p>Profesor de Sistemas Operativos</p>
          </div>
          <ul class="info-funcionario">
            <li>Cédula: 22222222</li>
            <li>Correo: franco.povea@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
            <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
          </div>
        </div>

        <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario">Lucía Fernández</p>
            <p>Personal de Limpieza</p>
          </div>
          <ul class="info-funcionario">
            <li>Cédula: 33333333</li>
            <li>Correo: lucia.fernandez@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
            <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
          </div>
        </div>

        <div class="funcionario">
          <div class="desc">
            <p class="nombre-funcionario">Carlos Gómez</p>
            <p>Personal de Limpieza</p>
          </div>
          <ul class="info-funcionario">
            <li>Cédula: 44444444</li>
            <li>Correo: carlos.gomez@itsp.edu.uy</li>
          </ul>
          <div class="botones">
            <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
            <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
          </div>
        </div>
      </div>

      <!-- BOTONES FLOTANTES -->
      <div class="botones-flotantes">
        <button onclick="location.href='#'"><i class="bi bi-person-plus"></i> Crear funcionario</button>
      </div>
    </main>
  </div>

  <script src="../scripts/menuHamburgesa.js"></script>
  <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>