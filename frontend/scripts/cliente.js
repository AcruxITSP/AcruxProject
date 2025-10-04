export async function client_parteDiario_fetchAll() {
    // objetos de ejemplo

    const registro1 = new Registro("2025-07-31 09:30:00", "El estudiante Thiago Diaz se retiró por dolor de c");
    const registro2 = new Registro("2025-06-23 14:23:00", "El estudiante Alejo Bottesch se retiró sin previo");

    const registros = [registro1, registro2];

    return registros;
}

class Registro {
    constructor(fechaHora, accion) {
        this.fechaHora = fechaHora;
        this.accion = accion;
    }
}
