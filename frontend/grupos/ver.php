<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Grupos</title>
    <link rel="stylesheet" href="../styles/styles.css">
</head>

<body id="body-grupos-ver">
    <main id="main-grupos-ver">
        <div class="scrollable-list" id="lista-targetas-grupos">
            <div name="targeta-grupo">
                <div name="encabezado">
                    <div class="marco-imagen"></div>
                    <h1 class="nombre-grupo">3MD</h1>
                </div>
                <div name="info-grupo">
                    <p><strong>Adscripto/a: </strong></p>
                    <p class="nombre-adscripta">Silvana Salvatierra</p>

                    <p><strong>Curso: </strong></p>
                    <p class="nombre-curso">Informática</p>
                </div>
                <div name="botones">
                    <input type="button" name="editar" value="E">
                    <input type="button" name="borrar" value="X">
                </div>
            </div>
            <div name="targeta-grupo">
                <div name="encabezado">
                    <div class="marco-imagen"></div>
                    <h1 class="nombre-grupo">3MD</h1>
                </div>
                <div name="info-grupo">
                    <p><strong>Adscripto/a: </strong></p>
                    <p class="nombre-adscripta">Silvana Salvatierra</p>

                    <p><strong>Curso: </strong></p>
                    <p class="nombre-curso">Informática</p>
                </div>
                <div name="botones">
                    <input type="button" name="editar" value="E">
                    <input type="button" name="borrar" value="X">
                </div>
            </div>
            <div name="targeta-grupo">
                <div name="encabezado">
                    <div class="marco-imagen"></div>
                    <h1 class="nombre-grupo">3MD</h1>
                </div>
                <div name="info-grupo">
                    <p><strong>Adscripto/a: </strong></p>
                    <p class="nombre-adscripta">Silvana Salvatierra</p>

                    <p><strong>Curso: </strong></p>
                    <p class="nombre-curso">Informática</p>
                </div>
                <div name="botones">
                    <input type="button" name="editar" value="E">
                    <input type="button" name="borrar" value="X">
                </div>
            </div>
            <div name="targeta-grupo">
                <div name="encabezado">
                    <div class="marco-imagen"></div>
                    <h1 class="nombre-grupo">3MD</h1>
                </div>
                <div name="info-grupo">
                    <p><strong>Adscripto/a: </strong></p>
                    <p class="nombre-adscripta">Silvana Salvatierra</p>

                    <p><strong>Curso: </strong></p>
                    <p class="nombre-curso">Informática</p>
                </div>
                <div name="botones">
                    <input type="button" name="editar" value="E">
                    <input type="button" name="borrar" value="X">
                </div>
            </div>
        </div>
        <div id="div-btn-agregar">
            <input type="button" id="btn-agregar-grupo" name="agregar" value="Agregar con template">
        </div>
    </main>

    <template id="tpl-targeta-grupo">
        <div name="targeta-grupo">
            <div name="encabezado">
                <div class="marco-imagen"></div>
                <h1 class="nombre-grupo"></h1>
            </div>
            <div name="info-grupo">
                <p><strong>Adscripto/a: </strong></p>
                <p class="nombre-adscripta">Silvana Salvatierra</p>

                <p><strong>Curso: </strong></p>
                <p class="nombre-curso">Informática</p>
            </div>
            <div name="botones">
                <input type="button" name="editar" value="E">
                <input type="button" name="borrar" value="X">
            </div>
        </div>
    </template>

    <script src="../scripts/crear_targeta_grupo.js"></script>
</body>

</html>