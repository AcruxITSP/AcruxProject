<?php
require '../scriptsPhp/globalFunctions.php';
session_start();
verificarInicioSesion();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
    <link rel="stylesheet" href="../styles/styles.css">
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
                <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="menuRecursos.php"><i class="fas fa-book"></i> Recursos</a>
                <a href="contacto.php"><i class="fas fa-envelope"></i> Contacto</a>
                <a href="mySchedule.php"><i class="fas fa-calendar-days"></i> Mi horario</a>
                <a href="parteDiario.php"><i class="fas fa-list"></i> Parte Diario</a>
            </nav>
        </aside>

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

            <h2>Contactos</h2>

            <h3>Nuestros contactos</h3>
        </main>
    </div>

    <!-- Script para el dropdown -->
    <script src="../scripts/indexDropMenu.js"></script>
</body>

</html>