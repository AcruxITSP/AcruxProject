<?php include '../util/sesiones.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Document</title>
</head>
<body id="body-ver-recursos">
    <main id="main-ver-recursos">
        <h1>Recursos Externos</h1>
        <div id="div-recursos-externos">
            
        </div>

        <h1 id="h-recursos-internos">Recursos Internos</h1>
        <div id="div-recursos-internos">
            
        </div>

        <?php if(esAdscripto()):?> <button onclick="location.href='crear.php'">Crear Recurso</button> <?php endif;?>
    </main>

    <template id="template-div-recurso-externo">
        <div>
            <p name="id">0</p>
            <img name="imagen">
            <div name="desc">
                <p name="tipo">Control</p>
                <p name="aula">Salon 5</p>
            </div>
            <div name="uso">
                <p name="libre">Libre: 1</p>
                <p name="ocupado">Ocupado: 0</p>
            </div>
            <div name="botones">
                <?php if(esAdscripto()):?> <button name="borrar">Borrar</button> <?php endif;?>
                <?php if(esAdscripto()):?> <button name="editar"><i class="bi bi-pencil"></i></button> <?php endif;?>
                <?php if(estaLogeado()):?> <button name="reservar">Reservar</button> <?php endif;?>
            </div>
        </div>
    </template>

    <template id="template-div-recurso-interno">
        <div>
            <p name="id">0</p>
            <img name="imagen">
            <div name="desc">
                <p name="tipo">Control</p>
            </div>
            <div name="espacios">
            </div>
            <div name="botones">
                <?php if(esAdscripto()):?> <button name="borrar">Borrar</button> <?php endif;?>
                <?php if(esAdscripto()):?> <button name="editar"><i class="bi bi-pencil"></i></button> <?php endif;?>
            </div>
        </div>
    </template>

    <template id="template-recurso-interno-espacio">
        <div>
            <p name="espacio"></p>
            <p name="cantidad"></p>
            <p name="disponibilidad"></p>
            <?php if(estaLogeado()):?> <a name="reservar" href="">Reservar</button> <?php endif;?>
        </div>
    </template>

    <script src="../scripts/recursos_ver.js"></script>
</body>
</html>