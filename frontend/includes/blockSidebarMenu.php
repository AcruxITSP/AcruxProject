<?php
include_once "rutaBase.php";
$root = BASE_PATH;
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h2>ITSP</h2>
    <button class="close-btn" onclick="toggleSidebar()">&times;</button>
  </div>
  <nav class="sidebar-nav">
    <a href="<?= $root ?>/general/index.php"><i class="fas fa-home"></i> Inicio</a>
    <a href="<?= $root ?>/general/menuRecursos.php"><i class="fas fa-book"></i> Dashboard</a>
    <a href="<?= $root ?>/general/contacto.php"><i class="fas fa-envelope"></i> Contacto</a>

    <!-- <a href="mySchedule.php"><i class="fas fa-calendar-days"></i> Mi horario</a> -->
    <!-- <a href="#"><i class="fas fa-list"></i> Parte Diario</a> -->
  </nav>
</aside>

<!-- Botón hamburguesa -->
<button class="hamburger" onclick="toggleSidebar()">
  <img src="<?= $root ?>/img/icons8-menú-48.png" alt="menu" class="hamburger-icon">
</button>

<!-- Overlay -->
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>