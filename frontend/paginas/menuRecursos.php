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
    <title>Recursos</title>
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

            <!-- Header usuario + título -->
            <div class="header">
                <h1>Recursos ITSP</h1>
                <div class="user-dropdown">
                    <div class="user-info" onclick="toggleDropdown()">
                        <i class="fas fa-user-circle"></i>
                        <?php echo "<span>" . $_SESSION["username"] . "</span>"; ?>
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
            </div>

            <!-- Tarjetas de recursos -->
            <div class="resources-container">
                <a href="planillaHorario/ver.php" class="resource-card">
                    <i class="fas fa-calendar-days icon-card"></i>
                    <span>Planilla de Horario</span>
                </a>
                <a href="materias/ver.php" class="resource-card">
                    <i class="fas fa-book icon-card"></i>
                    <span>Materias</span>
                </a>
                <a href="cursos/ver.php" class="resource-card">
                    <i class="fas fa-graduation-cap icon-card"></i>
                    <span>Cursos</span>
                </a>
                <a href="grupos/ver.php" class="resource-card">
                    <i class="fas fa-users icon-card"></i>
                    <span>Grupos</span>
                </a>
                <a href="aulas/ver.php" class="resource-card">
                    <i class="fas fa-chalkboard icon-card"></i>
                    <span>Espacios</span>
                </a>
                <a href="recursosAulas.php" class="resource-card">
                    <i class="fas fa-laptop icon-card"></i>
                    <span>Recursos de Aulas</span>
                </a>
                <a href="funcionarios.php" class="resource-card">
                    <i class="fas fa-user-tie icon-card"></i>
                    <span>Funcionarios</span>
                </a>
            </div>

        </main>
    </div>

    <script src="../scripts/indexDropMenu.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
</body>
</html>
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
    <title>Recursos</title>
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

            <!-- Header usuario + título -->
            <div class="header">
                <h1>Recursos ITSP</h1>
                <div class="user-dropdown">
                    <div class="user-info" onclick="toggleDropdown()">
                        <i class="fas fa-user-circle"></i>
                        <?php echo "<span>" . $_SESSION["username"] . "</span>"; ?>
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
            </div>

            <!-- Tarjetas de recursos -->
            <div class="resources-container">
                <a href="planillaHorario/ver.php" class="resource-card">
                    <i class="fas fa-calendar-days icon-card"></i>
                    <span>Planilla de Horario</span>
                </a>
                <a href="materias/ver.php" class="resource-card">
                    <i class="fas fa-book icon-card"></i>
                    <span>Materias</span>
                </a>
                <a href="cursos/ver.php" class="resource-card">
                    <i class="fas fa-graduation-cap icon-card"></i>
                    <span>Cursos</span>
                </a>
                <a href="grupos/ver.php" class="resource-card">
                    <i class="fas fa-users icon-card"></i>
                    <span>Grupos</span>
                </a>
                <a href="aulas/ver.php" class="resource-card">
                    <i class="fas fa-chalkboard icon-card"></i>
                    <span>Aulas</span>
                </a>
                <a href="recursosAulas.php" class="resource-card">
                    <i class="fas fa-laptop icon-card"></i>
                    <span>Recursos de Aulas</span>
                </a>
                <a href="funcionarios.php" class="resource-card">
                    <i class="fas fa-user-tie icon-card"></i>
                    <span>Funcionarios</span>
                </a>
            </div>

        </main>
    </div>

    <script src="../scripts/indexDropMenu.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
</body>
</html>
