<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2>ITSP</h2>
    <button class="close-btn" onclick="toggleSidebar()">&times;</button>
  </div>
  <nav class="sidebar-nav">
    <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
    <a href="menuRecursos.php"><i class="fas fa-book"></i> Dashboard</a>
    <a href="contacto.php"><i class="fas fa-envelope"></i> Contacto</a>

    <?php @session_start(); ?>
    <?php if (isset($_SESSION['username'])) : ?>
      <a href="../cuenta/logout.php"><i class="fas fa-envelope"></i>Cerrar Sesion</a>
    <?php else: ?>
      <a href="../cuenta/login.php"><i class="fas fa-envelope"></i>Iniciar Sesion</a>
    <?php endif; ?>
    <!-- <a href="mySchedule.php"><i class="fas fa-calendar-days"></i> Mi horario</a> -->
    <!-- <a href="#"><i class="fas fa-list"></i> Parte Diario</a> -->
  </nav>
</aside>