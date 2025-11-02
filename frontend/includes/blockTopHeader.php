<?php
$root = "/frontend";
?>

<!-- Top Header con dropdown -->
<header class="main-header">
  <div class="user-dropdown">
    <div class="user-info" onclick="toggleDropdown()">
      <i class="fas fa-user-circle"></i>
      <?php
      @session_start();
      //echo "<span>" . $_SESSION["username"] . "</span>";
      echo $_SESSION['username'] ?? '(Invitado)';
      ?>
      <i class="fas fa-caret-down"></i>
    </div>
    <div class="dropdown-menu" id="dropdownMenu">
      <a href="<?= $root ?>/general/myAccount.php">Mi cuenta</a>
      <a href="<?= $root ?>/general/configuracion.php">Configuración</a>
      <?php @session_start(); ?>
      <?php if (isset($_SESSION['username'])) : ?>
        <a id="btn-cerrar-sesion" href="../cuenta/logout.php">Cerrar sesión</a>
      <?php else: ?>
        <a id="btn-iniciar-sesion" href="../cuenta/login.php">Iniciar Sesión</a>
      <?php endif; ?>
    </div>
  </div>
</header>