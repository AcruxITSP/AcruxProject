export async function client_parteDiario_fetchAll() {
    // objetos de ejemplo

    const registro1 = new RegistroPartediario("2025-07-31 09:30:00", "El estudiante Thiago Diaz se retiró por dolor de c");
    const registro2 = new RegistroPartediario("2025-06-23 14:23:00", "El estudiante Alejo Bottesch se retiró sin previo");

    const registros = [registro1, registro2];

    return registros;
}

export async function client_cursos_fetchAll() {
    // objetos de ejemplo
    const materias1 = ["Sociologia", "Ingles", "Programacion", "Agricultura"];
    const materias2 = ["Natacion", "Gastronomia", "Sandwiches II", "Polimatizacion"];

    const registro1 = new RegistroCursos("Informática Bilingüe", 3, materias1);
    const registro2 = new RegistroCursos("Informática", 3, materias2);

    const registros = [registro1, registro2];

    return registros;
}

export async function client_materias_fetchAll() {
    // objetos de ejemplo
    const registro1 = new RegistroMateria("Sistemas Operativos");
    const registro2 = new RegistroMateria("Programacion II");
    const registro3 = new RegistroMateria("Refuerzo II");

    const registros = [registro1, registro2, registro3];

    return registros;
}

export async function client_materias_register(nombre) {
    const formData = new FormData();                                        
    formData.append("nombre", nombre);                                      

    const requestInit = {method: 'POST', body: formData};
    //const localizacionApi = '../backend/api/user/obtener_por_nombre.php';
    //const respuesta = await fetch(localizacionApi, requestInit);

    let respuesta;

    if (typeof formData.get("nombre") === "string"){
        respuesta = true;
    } else {
        respuesta = "Error";
    }

    return respuesta;    // La respuesta es true, o un error
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
    constructor(nombre){
        this.nombre = nombre;
    }
}