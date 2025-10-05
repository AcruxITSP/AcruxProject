<?php
require '../scriptsPhp/globalFunctions.php';
session_start();
verificarInicioSesion();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ITSP Dashboard</title>
  <link rel="stylesheet" href="../styles/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

  <!-- Botón hamburguesa -->
  <button class="hamburger" onclick="toggleSidebar()">
  <img src="img/icons8-menú-48.png" alt="menu" class="hamburger-icon">
</button>

  <div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <h2>ITSP</h2>
        <button class="close-btn" onclick="toggleSidebar()">&times;</button>
      </div>
      <nav class="sidebar-nav">
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="menuRecursos.php"><i class="fas fa-book"></i> Recursos</a>
        <a href="contacto.php"><i class="fas fa-envelope"></i> Contacto</a>
        <a href="mySchedule.php"><i class="fas fa-calendar-days"></i> Mi horario</a>
        <a href="parteDiario/ver.php"><i class="fas fa-list"></i> Parte Diario</a>
      </nav>
    </aside>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Contenido principal -->
    <main class="main-content">

      <!-- Top Header con dropdown -->
      <header class="main-header">
        <div class="user-dropdown">
          <div class="user-info" onclick="toggleDropdown()">
            <i class="fas fa-user-circle"></i>
            <?php
            echo "<span>" . $_SESSION["username"] . "</span>";
            ?>
            <i class="fas fa-caret-down"></i>
          </div>
          <div class="dropdown-menu" id="dropdownMenu">
            <a href="myAccount.php">Mi cuenta</a>
            <a href="configuracion.php">Configuración</a>
            <form action="../scriptsPhp/scriptCerrarSesion.php" method="post">
              <button id="btn-unLog" type="submit">Cerrar sesión</button>
            </form>
          </div>
        </div>
      </header>

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

</body>

</html>
