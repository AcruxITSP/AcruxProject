/**
 * Select que contiene los grupos disponibles.
 * Cada opción tiene un id (0,1,2, etc.) y un texto con el formato “(3 MD, 1 BE)”.
 */
const domSelectGrupo = document.getElementById("select-grupo");

/**
 * Div que contendrá los divs generados dinámicamente para mostrar las horas del grupo seleccionado.
 */
const domTablaHoras = document.getElementById("tabla-horas");

/**
 * Botones correspondientes a los días de la semana.
 * Al hacer clic en uno de ellos se actualizará la vista del horario de ese día.
 */
const domButtonLunes = document.getElementById("button-lunes");
const domButtonMartes = document.getElementById("button-martes");
const domButtonMiercoles = document.getElementById("button-miercoles");
const domButtonJueves = document.getElementById("button-jueves");
const domButtonViernes = document.getElementById("button-viernes");

/**
 * Botones de la interfaz para agregar o borrar horas, y el template HTML usado para crear nuevas filas.
 */
const domButtonAgregarHora = document.getElementById("button-agregar-hora");
const domButtonBorrarHora = document.getElementById("button-borrar-hora");
const domTemplateHora = document.getElementById("template-hora");

/**
 * Elementos relacionados con el modal para agregar una nueva hora.
 */
const domModalAgregarHora = document.getElementById('modal-agregar-hora');
const domFormAgregarHora = document.getElementById('form-agregar-hora');
const domAgregarHora_Materia = domFormAgregarHora.querySelector("[name='materia']");
const domAgregarHora_Profesor = domFormAgregarHora.querySelector("[name='profesor']");
const domAgregarHora_Espacio = domFormAgregarHora.querySelector("[name='espacio']");
const domModalAgregarHora_Cerrar = document.getElementById('modal-agregar-hora-cerrar');
const domAgregarHora_PeriodoInicioContainer = domFormAgregarHora.querySelector("[name='periodo-inicio-container']");
const domAgregarHora_ErrorProfeOcupado =  document.getElementById("modal-error-profe-ocupado");
const domAgregarHora_ErrorEspacioOcupado =  document.getElementById("modal-error-espacio-ocupado");

///////////////////////////////////////////////
// VARIABLES GLOBALES
// Se usan para mantener el estado actual de la UI y los datos cargados.

let dia = 'Lunes'; // Día actualmente seleccionado
let profesoresPorId = []; // Mapa de profesores por ID
let materiasPorId = [];   // Mapa de materias por ID
let idProfesoresPorIdMaterias = []; // Relación de profesores por materia
let numeroIntervaloAAgregar = 0; // Numero del intervalo donde se agregará una nueva hora
let periodos = []; // Lista de periodos horarios

function obtenerNumeroIntervaloAAgregar() {
	let num = numeroIntervaloAAgregar;
	if(num == null)
	{
		try
		{
			num = domAgregarHora_PeriodoInicioContainer.querySelector("select").value;
		}
		catch
		{
			if(!periodos) return null;
			num = periodos[0].numero;
		}
	}

	return num; 
}

///////////////////////////////////////////////
// EVENTOS DE GRUPO Y DÍA

// Cuando cambia el grupo seleccionado, se recargan los horarios.
domSelectGrupo.addEventListener('change', async e => {
	await traerYMostrarHorarios();
});

// Cada botón de día cambia el valor de `dia` y recarga los horarios correspondientes.
domButtonLunes.addEventListener('click', async e => { dia = 'Lunes'; await traerYMostrarHorarios(); });
domButtonMartes.addEventListener('click', async e => { dia = 'Martes'; await traerYMostrarHorarios(); });
domButtonMiercoles.addEventListener('click', async e => { dia = 'Miercoles'; await traerYMostrarHorarios(); });
domButtonJueves.addEventListener('click', async e => { dia = 'Jueves'; await traerYMostrarHorarios(); });
domButtonViernes.addEventListener('click', async e => { dia = 'Viernes'; await traerYMostrarHorarios(); });

////////////////////////////////////////
// UI - CREACIÓN Y MUESTRA DE HORAS

/**
 * Crea un div basado en el template 'template-hora' y lo llena con los datos del horario recibido.
 */
async function uiCrearTemplateHora(horario) {
	const domDivHorario = domTemplateHora.content.cloneNode(true).querySelector('div');
	const domHoraInicio = domDivHorario.querySelector("[name='hora-inicio']");
	const domHoraFinal = domDivHorario.querySelector("[name='hora-final']");
	const domNombreMateria = domDivHorario.querySelector("[name='nombre-materia']");
	const domNombreProfesor = domDivHorario.querySelector("[name='nombre-profesor']");
	const domNombreEspacio = domDivHorario.querySelector("[name='nombre-espacio']");
	const domButtonEditar = domDivHorario.querySelector("[name='button-editar']");

	// Asigna los valores al template
	domHoraInicio.innerText = horario.hora_inicio;
	domHoraFinal.innerText = horario.hora_final;
	domNombreMateria.innerText = horario.nombre_materia;
	domNombreProfesor.innerText = horario.nombre_profesor;
	domNombreEspacio.innerText = horario.nombre_espacio;

	// Asigna acción al botón de editar (actualmente en mantenimiento)
	if(domButtonEditar) domButtonEditar.onclick = () => { irAEditarModulo(horario.id_modulo); };

	// Marca visualmente si el profesor está ausente (aunque la función devuelve false por ahora)
	estaElProfeAusenteEn(horario.id_profesor, dia, horario.numero_intervalo)
	.then(estaAusente =>{
		if(estaAusente) domDivHorario.classList.add('hora-profe-ausente');
	});

	return domDivHorario;
}

/**
 * Agrega una hora (div) al contenedor principal de horarios.
 */
async function uiAgregarDivHora(horario) {
	const domDivHorario = await uiCrearTemplateHora(horario);
	domTablaHoras.appendChild(domDivHorario);
}

/**
 * Limpia la tabla y muestra todos los horarios actuales.
 */
function uiMostrarHoras(horarios) {
	domTablaHoras.innerHTML = '';
	horarios.forEach(horario => {
		uiAgregarDivHora(horario);
	});
}

/////////////////////////////////////////////
// FUNCIONES VARIAS

/**
 * (Placeholder) Muestra un mensaje indicando que la edición de módulos está en mantenimiento.
 */
function irAEditarModulo(idModulo) {
	window.location.href = `editar.php?id=${idModulo}`;
}

/**
 * Trae los horarios del grupo y día seleccionados desde el backend, y los muestra en la tabla.
 */
async function traerYMostrarHorarios() {
	const grupo = domSelectGrupo.value;
	console.log(`Mostrando horario del grupo ${grupo} en dia ${dia}`);

	let response = await fetch(`../../../backend/horarios/grupos.php?dia=${dia}&id_grupo=${grupo}`);
	response = await response.json();
	const horarios = response.value;

	uiMostrarHoras(horarios);

	// Calcula el número de intervalo siguiente al mayor actual, o null si no hay horarios.
	numeroIntervaloAAgregar = Math.max(...horarios.map(h => h.numero_intervalo)) + 1;
	if (numeroIntervaloAAgregar === -Infinity) numeroIntervaloAAgregar = null;
}

/**
 * Función temporal para borrar una hora (no implementada aún).
 */
async function borrarHoraAsync() {
	const resultadoDeAdvertencia = await Swal.fire({
        title: "Borrar Hora",
        text: "Borrara la ultima hora de este grupo para este dia.",
        icon: "info",

        showCancelButton: true,
        cancelButtonText: "Cancelar",
        confirmButtonText: "Borrar"
    });

    if(!resultadoDeAdvertencia.isConfirmed) return;

	const formData = new FormData();
	formData.append("nombre_dia", dia);
	formData.append("id_grupo", domSelectGrupo.value);
	let respuesta = await fetch("../../../backend/horarios/grupos_borrar_hora.php", {method: "POST", body: formData});
	respuesta = await respuesta.json();

	if (!respuesta.ok)
	{
		switch(respuesta.value)
		{
			default:
				Swal.fire({
					title: "Error Desconocido",
					text: respuesta.data,
					icon: "error"
				});
		}
	}

	await traerYMostrarHorarios();
}

// Estas funciones son placeholders, retornan siempre false.
async function estaElProfeLibreEnAsync(idProfe, dia, numeroIntervalo)
{
	let respuesta = await fetch(`../../../backend/horarios/esta_profe_libre.php?id_profesor=${idProfe}&nombre_dia=${dia}&numero_intervalo=${numeroIntervalo}`);
	respuesta = await respuesta.json();
	return respuesta.value;
}

async function estaElProfeAusenteEn(idProfe, dia, numeroIntervalo)
{
	let respuesta = await fetch(`../../../backend/horarios/esta_profe_ausente.php?id_profesor=${idProfe}&nombre_dia=${dia}&numero_intervalo=${numeroIntervalo}`);
	respuesta = await respuesta.json();
	return respuesta.value;
}

async function estaElEspacioLibreEnAsync(idEspacio, dia, numeroIntervalo)
{
	let respuesta = await fetch(`../../../backend/horarios/esta_espacio_libre.php?id_espacio=${idEspacio}&nombre_dia=${dia}&numero_intervalo=${numeroIntervalo}`);
	respuesta = await respuesta.json();
	return respuesta.value;
}

/////////////////////////////////////////////
// MODAL CREAR HORA

/**
 * Muestra el modal para agregar una nueva hora, configurando los selects de materia, profesor y periodo.
 */
async function mostrarModalAgregarHora() {
	// En caso que no hayan periodos generados el programa puede crashear.
	// Informamos al usuario que debe registrar periodos
	if(!periodos)
	{
		Swal.fire({
			title: "Periodos",
			text: "No hay periodos registrados en el sistema por lo que no puedes agregar horarios.",
			icon: "error"
		});
		return;
	}

	await cambiarProfesSegunMateria();
	await validarDisponibilidadEspacio();
	await validarDisponibilidadProfeAsync();

	// Si no hay intervalo definido, se crea un select con los periodos disponibles
	if (numeroIntervaloAAgregar == null) {
		let periodoInicioInnerHtml = `
		<label for="numero_intervalo">Inicio</label>
		<select name="numero_intervalo">`;
		periodos.forEach(periodo => {
			periodoInicioInnerHtml += `<option value="${periodo.numero}">${periodo.entrada}</option>`;
		});
		periodoInicioInnerHtml += `</select>`;
		domAgregarHora_PeriodoInicioContainer.innerHTML = periodoInicioInnerHtml;
		domAgregarHora_PeriodoInicioContainer.children[1].addEventListener('change', async e => {
			await validarDisponibilidadEspacio();
			await validarDisponibilidadProfeAsync();
		});
	} else {
		domAgregarHora_PeriodoInicioContainer.innerHTML = '';
	}

	domModalAgregarHora.style.display = 'flex';
}

/**
 * Oculta el modal de agregar hora.
 */
function esconderModalAgregarHora() {
	domModalAgregarHora.style.display = 'none';
}

/**
 * Valida si el profesor seleccionado está disponible.
 */
async function validarDisponibilidadProfeAsync() {
	const idProfesor = domAgregarHora_Profesor.value;
	const estaElProfeLibre = await estaElProfeLibreEnAsync(idProfesor, dia, obtenerNumeroIntervaloAAgregar());
	if (!estaElProfeLibre)
		domAgregarHora_ErrorProfeOcupado.style.display = 'block';
	else
		domAgregarHora_ErrorProfeOcupado.style.display = 'none';
}

/**
 * Valida si el espacio seleccionado está disponible.
 */
async function validarDisponibilidadEspacio() {
	const idEspacio = domAgregarHora_Espacio.value;
	const estaElEspacioLibre = await estaElEspacioLibreEnAsync(idEspacio, dia, obtenerNumeroIntervaloAAgregar());
	if (!estaElEspacioLibre)
		domAgregarHora_ErrorEspacioOcupado.style.display = 'block';
	else
		domAgregarHora_ErrorEspacioOcupado.style.display = 'none';
}

/**
 * Actualiza el listado de profesores según la materia seleccionada.
 */
async function cambiarProfesSegunMateria() {
	domAgregarHora_Profesor.innerHTML = '';
	const idMateria = Number(domAgregarHora_Materia.value);
	const idProfesoresPorIdMateria = idProfesoresPorIdMaterias[idMateria];

	// Genera las opciones de profesor para esa materia
	idProfesoresPorIdMateria.forEach(idProfesor => {
		const profesor = profesoresPorId[idProfesor];
		domAgregarHora_Profesor.innerHTML += `<option value="${idProfesor}">${profesor.nombre} ${profesor.apellido}</option>`;
	});

	await validarDisponibilidadProfeAsync();
}

// Listeners para actualizar validaciones dinámicas dentro del modal
domAgregarHora_Materia.addEventListener('change', async e => await cambiarProfesSegunMateria());
domAgregarHora_Profesor.addEventListener('change', async e => await validarDisponibilidadProfeAsync());
domAgregarHora_Espacio.addEventListener('change', async e => await validarDisponibilidadEspacio());
domModalAgregarHora_Cerrar.addEventListener('click', async e => esconderModalAgregarHora());

////////////////////////////////////////////////////////////////////
// INICIALIZACIÓN

async function inicializar() {
	const grupoSeleccionado = domSelectGrupo.value;

	let respuesta = await fetch("../../../backend/horarios/grupos_init.php");
	respuesta = await respuesta.json();
	const grupos = respuesta.value.grupos;

	profesoresPorId = respuesta.value.profesores_por_id;
	materiasPorId = respuesta.value.materias_por_id;
	idProfesoresPorIdMaterias = respuesta.value.id_profesores_por_id_materias;
	periodos = respuesta.value.periodos;
	const espaciosLibres = respuesta.value.espacios_libres;
	const idMaterias = Object.keys(materiasPorId);

	// Recrea las opciones del select de grupos
	domSelectGrupo.innerHTML = '';
	grupos.forEach(grupo => {
		const selectedAttr = grupo.id_grupo == grupoSeleccionado ? 'selected' : '';
		domSelectGrupo.innerHTML += `<option value='${grupo.id_grupo}' ${selectedAttr}>${grupo.texto}</option>`;
	});

	// Rellena el select de materias
	domAgregarHora_Materia.innerHTML = '';
	idMaterias.forEach(idMateria => {
		const materia = materiasPorId[idMateria];
		domAgregarHora_Materia.innerHTML += `<option value="${idMateria}">${materia}</option>`;
	});

	// Rellena el select de espacios libres
	domAgregarHora_Espacio.innerHTML = '';
	espaciosLibres.forEach(espacioLibre => {
		domAgregarHora_Espacio.innerHTML += `<option value='${espacioLibre.id_espacio}'>${espacioLibre.tipo} ${espacioLibre.numero ?? ''}</option>`;
	});

	// Asigna los eventos a los botones principales
	if(domButtonAgregarHora) domButtonAgregarHora.onclick = async () => await mostrarModalAgregarHora();
	if (domButtonBorrarHora) domButtonBorrarHora.onclick = async () => await borrarHoraAsync();

	// Muestra los horarios del grupo actual
	await traerYMostrarHorarios();
}

////////////////////////////////////////////////////////////////////
// AGREGAR HORA (con reinicialización posterior)

domFormAgregarHora.addEventListener('submit', async e => {
	e.preventDefault();
	const formData = new FormData(domFormAgregarHora);

	if (numeroIntervaloAAgregar != null) formData.append("numero_intervalo", numeroIntervaloAAgregar);

	formData.append("nombre_dia", dia);
	formData.append("grupo", domSelectGrupo.value);

	let respuesta = await fetch('../../../backend/horarios/agregar_modulo.php', {
		method: "POST",
		body: formData
	});
	respuesta = await respuesta.json();

	if (respuesta.ok) {
		Swal.fire({
			title: "Hora Agregada",
			text: "Has registrado la hora correctamente.",
			icon: "success",
		}).then(async () => {
			esconderModalAgregarHora();
			await inicializar();
			await traerYMostrarHorarios();
		});
	} else {
		switch(respuesta.value) {
			case "HORA_INSUFICIENTE":
				Swal.fire({
					title: "Hora Insuficiente",
					text: "No hay una hora registrada en el sistema para el inicio del módulo.\n" +
						  "Intente registrar horas nuevas.",
					icon: "error"
				}).then(() => esconderModalAgregarHora());
				break;
			default:
				Swal.fire({
					title: "Error Desconocido",
					text: "Un error desconocido ha ocurrido",
					icon: "error"
				}).then(() => esconderModalAgregarHora());
		}
	}
});

////////////////////////////////////////////////////////////////////
// CARGA INICIAL

document.addEventListener('DOMContentLoaded', async e => {
	await inicializar();
});
