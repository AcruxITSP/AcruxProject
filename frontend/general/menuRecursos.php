<?php include '../util/sesiones.php'; ?>
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
    <i class="fas fa-bars"></i> 
    </button>

    <div class="dashboard-container">
        <!-- Traer el codigo del side-bar -->
        <?php include_once dirname(__FILE__) . '/../includes/blockSidebarMenu.php' ?>

        <!-- Contenido principal -->
        <main class="main-content">

            <!-- Traer el codigo del "top header" -->
            <?php include_once dirname(__FILE__) . '/../includes/blockTopHeader.php' ?>

            <h2>Recursos ITSP</h2>

            <!-- Tarjetas de recursos -->
            <div class="resources-container">
                <a href="../recursos/ver.php" class="resource-card">
                    <i class="fas fa-laptop icon-card"></i>
                    <span>Recursos</span>
                </a>
                <a href="../recursos/ver.php#h-recursos-internos" class="resource-card">
                    <i class="fas fa-chalkboard icon-card"></i>
                    <span>Espacios</span>
                </a>
                <a href="../horarios/grupos.php" class="resource-card">
                    <i class="fas fa-calendar-days icon-card"></i>
                    <span>Horarios</span>
                </a>
                <a href="../cursos/ver.php" class="resource-card">
                    <i class="fas fa-graduation-cap icon-card"></i>
                    <span>Cursos</span>
                </a>
                <a href="../asignaturas/ver.php" class="resource-card">
                    <i class="fas fa-book icon-card"></i>
                    <span>Asignaturas</span>
                </a>
                <a href="#"class="resource-card">
                    <i class="fas fa-users icon-card"></i>
                    <span>Grupos</span>
                </a>
                <a href="#" class="resource-card">
                    <i class="fas fa-user-tie icon-card"></i>
                    <span>Ausencias</span>
                </a>
                <a href="../funcionarios/ver.php"  class="resource-card">
                    <i class="fas fa-user-tie icon-card"></i>
                    <span>Funcionarios</span>
                </a>
                <?php if(esProfesor()): ?>
                    <a href="mySchedule.php" class="resource-card">
                        <i class="fas fa-user-tie icon-card"></i>
                        <span>Mi horario</span>
                    </a>
                <?php endif; ?>

                <?php if(estaLogeado()): ?>
                    <a href="../reservas/my.php" class="resource-card">
                        <i class="fas fa-user-tie icon-card"></i>
                        <span>Mis reservas</span>
                    </a>
                <?php endif; ?>
               
            </div>

        </main>
    </div>

    <script src="../scripts/indexDropMenu.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
</body>

</html>