<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ITSP Dashboard</title>

  <link rel="stylesheet" href="../styles/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body id="body-index">

  <div class="dashboard-container">
    <!-- Traer el codigo del side-bar -->
    <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>

    <!-- Contenido principal -->
    <main class="main-content">

      <!-- Traer el codigo del "top header" -->
      <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>

      <!-- Imagen de ITSP -->
      <div class="banner-img">
        <img src="../img/banner.jpg" alt="Imagen ITSP">
      </div>

      <!-- Tarjetas informativas -->
      <section class="card-section">
        <div class="card">
          <h3>450</h3>
          <p>Estudiantes</p>
        </div>
        <div class="card">
          <h3>15</h3>
          <p>Espacios</p>
        </div>
        <div class="card">
          <h3>5</h3>
          <p>Turnos</p>
        </div>
        <div class="card">
          <h3>+30</h3>
          <p>Recursos</p>
        </div>
      </section>

      <!-- Mapa de la ubicación (fuera de card-section) -->
      <section class="mapa">
        <h2>Encuentranos aqui</h2>
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3371.7246905661304!2d-58.086378024515234!3d-32.31924937385833!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95afcbfdd7fbb8eb%3A0xa8125d5f102fc8e4!2sITS%20Paysandu%20UTU!5e0!3m2!1ses-419!2suy!4v1759148352509!5m2!1ses-419!2suy"
          width="100%"
          height="450"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </section>

      <script src="../scripts/menuHamburgesa.js"></script>
      <script src="../scripts/dropdownMenu.js"></script>
    </main>
  </div>
</body>

</html>