/**
 * Asocia un filtrado inteligente basado en expresiones regulares (regex) a un campo <input>.
 * Si no existe un elemento <datalist> asociado, lo crea automáticamente.
 *
 * @param {HTMLInputElement} input - El elemento de entrada al que se le aplicará el autocompletado inteligente.
 * @param {string[]} allOptions - La lista completa de opciones posibles.
 */
function attachSmartDatalist(input, allOptions)
{
	// Se genera un ID único para el <datalist> usando un número aleatorio convertido a base 36
	listId = `datalist-${Math.random().toString(36).slice(2, 9)}`;

	// Se crea un nuevo elemento <datalist> en el DOM
	datalist = document.createElement('datalist');
	datalist.id = listId;

	// Se añade el <datalist> al cuerpo del documento para que esté disponible globalmente
	document.body.appendChild(datalist);

	// Se vincula el input con este datalist recién creado
	input.setAttribute('list', listId);

	// Se llenan inicialmente todas las opciones en el datalist
	allOptions.forEach(option => {
		const optionTag = document.createElement('option');
		optionTag.value = option;
		datalist.appendChild(optionTag);
	});

	// Se agrega un evento que escucha cada vez que el usuario escribe en el input
	input.addEventListener('input', () =>
	{
		const query = input.value.trim(); // Se obtiene el texto actual, eliminando espacios innecesarios
		datalist.innerHTML = ''; // Se vacía el datalist para actualizarlo con nuevas coincidencias

		// Si el usuario no escribió nada, se muestran todas las opciones originales
		if (!query)
		{
			allOptions.forEach(option => {
				const optionTag = document.createElement('option');
				optionTag.value = option;
				datalist.appendChild(optionTag);
			});
			return; // No hay filtro, se sale del evento
		}

		// Filtra las opciones usando la función matchesQuery, que aplica coincidencias por palabra
		const filtered = allOptions.filter(opt => matchesQuery(opt, query));

		// Se añaden al datalist únicamente las coincidencias encontradas
		for (const match of filtered)
		{
			const option = document.createElement('option');
			option.value = match;
			datalist.appendChild(option);
		}
	});
}

/**
 * Devuelve true si *alguna* palabra de la búsqueda (`query`) aparece en el texto (`item`).
 * Ejemplo: "Cable HDMI" coincide con "HDMI Cable".
 */
function matchesQuery(item, query) {
	// Se separa la consulta del usuario en palabras (por espacios) y se escapan caracteres especiales de regex
	const words = query
		.trim()
		.split(/\s+/)
		.map(w => w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')); // Evita errores en la expresión regular

	// Se genera un patrón que busca cualquier palabra (usa '|' como OR en regex)
	const pattern = words.join('|');

	// Se crea la expresión regular insensible a mayúsculas/minúsculas
	const regex = new RegExp(pattern, 'i');

	// Retorna true si el texto (`item`) contiene alguna de las palabras del patrón
	return regex.test(item);
}
