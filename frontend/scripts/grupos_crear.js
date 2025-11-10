const domInputSelectCurso = document.getElementById("select-curso");
const domInputSelectAdscrito = document.getElementById("select-adscrito");

/* Array con datos de adscritos. Ejemplo */
const jsonStringAdscritos = '[{"id_adscrito": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_adscrito": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_adscrito": "3", "nombre": "Enrico", "apellido": "Pucci"}]';
const adscritos = JSON.parse(jsonStringAdscritos);

/* Array con cursos. Ejemplo */
const jsonStringCursos = '[{"id_curso": "1", "nombre": "Informatica"}, {"id_curso": "2", "nombre": "Informatica Bilingue"}, {"id_curso": "3", "nombre": "Gastrobomia"}]';
const cursos = JSON.parse(jsonStringCursos);

function IListAdscritosOptions(adscritos){
    adscritos.forEach(adscrito => {
        const option = document.createElement("option");

        option.value = `${adscrito.id_adscrito}`;
        option.innerText = `${adscrito.nombre} ${adscrito.apellido}`;

        domInputSelectAdscrito.appendChild(option);
    });
}

function IListCursosOptions(cursos){
    cursos.forEach(curso => {
        const option = document.createElement("option");

        option.value = `${curso.id_curso}`;
        option.innerText = `${curso.nombre}`;

        domInputSelectCurso.appendChild(option);
    });
}

IListAdscritosOptions(adscritos);
IListCursosOptions(cursos);