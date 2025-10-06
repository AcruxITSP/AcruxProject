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
    <title>Mi horario</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Botón hamburguesa -->
    <button class="hamburger" onclick="toggleSidebar()">
        <img src="img/icons8-menú-48.png" alt="menu" class="hamburger-icon">
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>ITSP</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="menuRecursos.php"><i class="fas fa-book"></i> Recursos</a>
                <a href="contacto.php"><i class="fas fa-envelope"></i> Contacto</a>
                <a href="mySchedule.php"><i class="fas fa-calendar-days"></i> Mi horario</a>
                <a href="parteDiario/ver.php"><i class="fas fa-list"></i> Parte Diario</a>
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

            <h2>Su horario de esta semana: </h2>
            <br>
            <p>Aún no hay un horario definido</p>
        </main>
    </div>
<!-- Script para menu hamburguesa-->
    <script src="scripts/menuHamburgesa.js"></script>
    <!-- Script para el dropdown -->
    <script src="../scripts/indexDropMenu.js"></script>
</body>

</html>