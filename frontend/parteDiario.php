<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parte Diario</title>
    <link rel="stylesheet" href="styles/styles.css">

</head>
<body> <!-- Overlay -->
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
                <a href="parteDiario.php"><i class="fas fa-list"></i> Parte Diario</a>
            </nav>
        </aside>

        
    <h2>Parte Diario</h2>
    <ul>
        <li>Aqui</li>
        <li>Se van a listar</li>
        <li>Todos los registros</li>
        <li>Del parte diario</li>
    </ul>
    <a href="index.php">Volver al menu</a>
    
<!-- Script para menu hamburguesa-->
    <script src="scripts/menuHamburgesa.js"></script></body>
</html>