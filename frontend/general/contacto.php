<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body id="body-contactos" class="menues-incluidos">
    <div id="menues">
        <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
        <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>
    </div>

    <div id="main-content">
        <!-- Contenido principal -->
        <main class="main-content">

            <h2>Contactos</h2>

            <h3>Contacto del ITSP</h3>

            <div id="contacto-itsp">
                <div id="logo-itsp">
                    <img src="../img/utu.png">
                </div>
                <div class="contactos">
                    <p class="telefono"><strong>Tel:</strong> 4724 2917 - 091 731 085</p>
                    <p class="e-mail"><strong>E-mail:</strong> itsp@utu.edu.uy</p>
                    <p class="instagram"><strong>Instagram:</strong> its_utu.paysandu</p>
                </div>
            </div>

            <h3>Nuestros contactos</h3>

            <div id="contactos-equipo">
                <div class="targeta-contacto">
                    <img src="../img/foto_alejo.jpg">
                    <div class="info">
                        <h2 class="nombre">Alejo Bottesch</h2>
                        <p class="area-especializacion">Desarrollador Backend</p>
                    </div>
                    <div class="contactos">
                        <p class="telefono"><strong>Tel:</strong> 099 389 992</p>
                        <p class="e-mail"><strong>E-mail:</strong> alejobottesch.public@gmail.com</p>
                    </div>
                </div>

                <div class="targeta-contacto">
                    <img src="../img/foto_nehuel.jpg">
                    <div class="info">
                        <h2 class="nombre">Nehuel Acosta</h2>
                        <p class="area-especializacion">Desarrollador Backend</p>
                    </div>
                    <div class="contactos">
                        <p class="telefono"><strong>Tel:</strong> 097 962 771</p>
                        <p class="e-mail"><strong>E-mail:</strong> nehuelacosta@gmail.com</p>
                    </div>
                </div>

                <div class="targeta-contacto">
                    <img src="../img/foto_sofia.jpg">
                    <div class="info">
                        <h2 class="nombre">Sofía Verocai</h2>
                        <p class="area-especializacion">Desarrolladora Frontend</p>
                    </div>
                    <div class="contactos">
                        <p class="telefono"><strong>Tel:</strong> 092 992 900</p>
                        <p class="e-mail"><strong>E-mail:</strong> sofiaverocai10@gmail.com</p>
                    </div>
                </div>

                <div class="targeta-contacto">
                    <img src="../img/foto_thiago.jpg">
                    <div class="info">
                        <h2 class="nombre">Thiago Díaz</h2>
                        <p class="area-especializacion">Desarrollador Frontend</p>
                    </div>
                    <div class="contactos">
                        <p class="telefono"><strong>Tel:</strong> 097 071 420</p>
                        <p class="e-mail"><strong>E-mail:</strong> diazthiago4455@gmail.com</p>
                    </div>
                </div>

                <div class="targeta-contacto">
                    <img src="../img/foto_michel.jpg">
                    <div class="info">
                        <h2 class="nombre">Michel de Agustini</h2>
                        <p class="area-especializacion">Desarrollador Frontend</p>
                    </div>
                    <div class="contactos">
                        <p class="telefono"><strong>Tel:</strong> 097 012 707</p>
                        <p class="e-mail"><strong>E-mail:</strong> micho2017dag21@gmail.com</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Script para el dropdown -->
    <script src="../scripts/indexDropMenu.js"></script>
    <script src="../scripts/menuHamburgesa.js"></script>
    <script src="../scripts/dropdownMenu.js"></script>
</body>

</html>