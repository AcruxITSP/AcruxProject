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
      <form action="../scriptsPhp/scriptCerrarSesion.php" method="post">
        <button id="btn-unLog" type="submit">Cerrar sesión</button>
      </form>
    </div>
  </div>
</header>