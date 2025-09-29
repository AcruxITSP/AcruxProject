<?php
require 'globalFunctions.php';
session_start();
verificarInicioSesion();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ITSP Dashboard</title>
  <link rel="stylesheet" href="styles/styles.css">
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
        <a href="#"><i class="fas fa-home"></i> Inicio</a>
        <a href="#"><i class="fas fa-info-circle"></i> Información</a>
        <a href="#"><i class="fas fa-book"></i> Recursos</a>
        <a href="#"><i class="fas fa-envelope"></i> Contacto</a>
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
            <a href="#">Cambiar usuario</a>
            <form action="scriptCerrarSesion.php" method="post">
              <button id="btn-unLog" type="submit">Cerrar sesión</button>
            </form>
          </div>
        </div>
      </header>

      <!-- Imagen de ITSP -->
      <div class="banner-img">
        <img src="img/banner.jpg" alt="Imagen ITSP">
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
    </main>
  </div>

<script src="scripts/menuHamburgesa.js"></script>
<script src="scripts/dropdownMenu.js"></script>

</body>

</html>
