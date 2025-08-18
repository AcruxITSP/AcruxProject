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

  <div class="dashboard-container">
    
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>ITSP</h2>
      </div>
      <nav class="sidebar-nav">
        <a href="#"><i class="fas fa-home"></i> Inicio</a>
        <a href="#"><i class="fas fa-info-circle"></i> Información</a>
        <a href="#"><i class="fas fa-book"></i> Recursos</a>
        <a href="#"><i class="fas fa-envelope"></i> Contacto</a>
      </nav>
    </aside>

    <!-- Contenido principal -->
    <main class="main-content">

      <!-- Top Header con dropdown -->
      <header class="main-header">
        <div class="user-dropdown">
          <div class="user-info" onclick="toggleDropdown()">
            <i class="fas fa-user-circle"></i>
            <span>Usuario</span>
            <i class="fas fa-caret-down"></i>
          </div>
          <div class="dropdown-menu" id="dropdownMenu">
            <a href="#">Cambiar usuario</a>
            <a href="#">Cerrar sesión</a>
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
          <h3>3450</h3>
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

  <!-- Script para el dropdown -->
  <script>
    function toggleDropdown() {
      const dropdown = document.getElementById('dropdownMenu');
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    window.addEventListener('click', function(e) {
      const dropdown = document.getElementById('dropdownMenu');
      if (!e.target.closest('.user-dropdown')) {
        dropdown.style.display = 'none';
      }
    });
  </script>

</body>
</html>
