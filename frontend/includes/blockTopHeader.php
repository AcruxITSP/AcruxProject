<?php
// Comprobar si la carpeta actual es "frontend/general"
$pattern = '/(\\|\/)general$/';
$string = __DIR__;

$coincidencias = preg_match($pattern, $string);
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
      <?php if ($coincidencias == 1): ?>
        <a href="myAccount.php">Mi cuenta</a>
        <a href="configuracion.php">Configuración</a>
      <?php else: ?>
        <a href="../general/myAccount.php">Mi cuenta</a>
        <a href="../general/configuracion.php">Configuración</a>
      <?php endif; ?>
      <form action="../scriptsPhp/scriptCerrarSesion.php" method="post">
        <button id="btn-unLog" type="submit">Cerrar sesión</button>
      </form>
    </div>
  </div>
</header>