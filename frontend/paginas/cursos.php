<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos</title>
</head>

<body>
    <style>
        /* Esto se puede sobrescribir nomas */
        form {
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        input:not(#agregarCurso, #materia-nombre) {
            margin-right: 100%;
            margin-bottom: 1rem;
        }
    </style>

    <h2>Cursos</h2>

    <label for="agregarCurso">Agregar Curso</label>
    <input type="checkbox" id="agregarCurso">

    <template id="tpl-form-cursos">
        <form>
            <label for="curso-nombre">Nombre:</label>
            <input type="text" id="curso-nombre">

            <label for="curso-duracionAnios">Duracion (años):</label>
            <input type="number" id="curso-duracionAnios">

            <input type="submit">
        </form>
    </template>

    <template id="tpl-form-materias">
        <form>
            <label for="materia-nombre">Agregar materia:</label>
            <input type="text" id="materia-nombre">

            <input type="submit">
        </form>
    </template>

    <template id="tpl-lista-cursos">
        <ul class="tpl-list">
            <li>
                <h3></h3>
                <ul class="lista-materias"></ul>
            </li>
        </ul>
    </template>

    <div id="form-cursos"></div>

    <div id="listaCursos"></div>

    <p id="errorMsg"></p>
    <a href="index.php">Volver al menu</a>

    <script type="module" src="../scripts/paginaCursos.js"></script>
</body>

</html>