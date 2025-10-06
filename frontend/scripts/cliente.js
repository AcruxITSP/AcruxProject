/* Objetos de ejemplo */

export async function client_parteDiario_fetchAll() {

    const registro1 = new RegistroPartediario("2025-07-31 09:30:00", "El estudiante Thiago Diaz se retiró por dolor de c");
    const registro2 = new RegistroPartediario("2025-06-23 14:23:00", "El estudiante Alejo Bottesch se retiró sin previo");

    const registros = [registro1, registro2];

    return registros;
}

export async function client_cursos_fetchAll() {
    const materias1 = ["Sociologia", "Ingles", "Programacion", "Agricultura"];
    const materias2 = ["Natacion", "Gastronomia", "Sandwiches II", "Polimatizacion"];

    const registro1 = new RegistroCursos("Informática Bilingüe", 3, materias1);
    const registro2 = new RegistroCursos("Informática", 3, materias2);

    const registros = [registro1, registro2];

    return registros;
}

export async function client_materias_fetchAll() {
    const registro1 = new RegistroMateria("Sistemas Operativos");
    const registro2 = new RegistroMateria("Programacion II");
    const registro3 = new RegistroMateria("Refuerzo II");

    const registros = [registro1, registro2, registro3];

    return registros;
}

export async function client_materias_register(sendedFormData) {
    const formData = new FormData();
    formData.append("nombre", sendedFormData.get("nombre"));

    const requestInit = { method: 'POST', body: formData };
    //const localizacionApi = '../backend/api/user/obtener_por_nombre.php';
    //const respuesta = await fetch(localizacionApi, requestInit);

    let respuesta;

    if (typeof formData.get("nombre") === "string") {
        respuesta = true;
    } else {
        respuesta = "Error";
    }

    return respuesta;    // La respuesta es true, o un error
}

export async function client_aulas_fetchAll() {
    const registro1 = new RegistroAula('2B', 'PB', 'Informática', 25);
    const registro2 = new RegistroAula('1A', '1', 'Lab. Física', 20);
    const registro3 = new RegistroAula('3D', '2', 'General', 40);

    const registros = [registro1, registro2, registro3];

    return registros;
}

export async function client_hora_fetchAll() {
    const diasDeClase = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes"];
    let registros = [];

    for (let i = 0; i < diasDeClase.length; i++) {
        for (let j = 1; j <= 8; j++) {
            // Por comodiad, guardo los intervalos como numeros, pero el programa debe
            // recibir los intervalos en el formato "horaEntrada - horaSalida"
            // Ej: "7:00 - 7:45"
            const registro = new Hora(j, diasDeClase[i]);
            registros.push(registro);
        }
    }

    return registros;
}

class RegistroPartediario {
    constructor(fechaHora, accion) {
        this.fechaHora = fechaHora;
        this.accion = accion;
    }
}

class RegistroCursos {
    constructor(nombre, duracion, materias) {
        this.nombre = nombre;
        this.duracion = duracion;
        this.materias = materias;
    }
}

class RegistroMateria {
    constructor(nombre) {
        this.nombre = nombre;
    }
}

class RegistroAula {
    constructor(codigo, piso, proposito, cantidadSillas){
        this.codigo = codigo;
        this.piso = piso;
        this.proposito = proposito;
        this.cantidadSillas = cantidadSillas;
    }
}

class Hora {
    constructor(intervalo, dia){
        this.intervalo = intervalo;
        this.dia = dia;
    }
}