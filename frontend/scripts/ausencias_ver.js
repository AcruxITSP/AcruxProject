const domTemplateTarjetaUsuario = document.getElementById("tpl-tarjeta-ausencia-usuario");
const domDivAusenciasUsuarios = document.getElementById("scrollable-list-ausencias-usuarios");
const domTemplateTarjetaAusencia = document.getElementById("tpl-targeta-ausencia");
const domDivAusencias = document.getElementById("scrollable-list-ausencias");

// Objetos de ausencia e intervaloAusencia de ejemplo
const jsonAusencia = '{"id_ausencia": "1", "id_profesor": "1", "motivo": "Esta fue hecha con template"}';
const ausencia= JSON.parse(jsonAusencia);

const jsonIntervaloAusencia = '{"id_intervalo_ausencia": "1", "fecha": "05/06/2025", "periodo_inicio": "8:40", "periodo_final": "9:25"}';
const intervaloAusencia = JSON.parse(jsonIntervaloAusencia);

/* Funciones */

function uiCrearTarjetaUsuario(usuario){
    const domDivTarjeta = domTemplateTarjetaUsuario.content.cloneNode(true).children[0];

    const domNombre = domDivTarjeta.querySelector("[name='nombre']");

    domNombre.innerText = `${usuario.nombre} ${usuario.apellido}`;
    
    domDivAusenciasUsuarios.appendChild(domDivTarjeta);
}

function uiCrearTarjetaAusencia(ausencia, intervaloAusencia){
    const domDivTarjeta = domTemplateTarjetaAusencia.content.cloneNode(true).children[0];

    const domFecha = domDivTarjeta.querySelector("[name='fecha']");
    const domIntervalo = domDivTarjeta.querySelector("[name='intervalo']");
    const domMotivo = domDivTarjeta.querySelector("[name='motivo']");

    domFecha.innerText = `${intervaloAusencia.fecha}`;
    domIntervalo.innerText = `${intervaloAusencia.periodo_inicio} - ${intervaloAusencia.periodo_final}`;
    domMotivo.innerText = `${ausencia.motivo}`;
    
    domDivAusencias.appendChild(domDivTarjeta);
}

uiCrearTarjetaAusencia(ausencia, intervaloAusencia);

async function inicializar()
{
    let response = await fetch("../../backend/usuarios/profesores.php");
    response = await response.json();

    const profesores = response.value;
    profesores.forEach(profesor => {
        uiCrearTarjetaUsuario(profesor);
    });
}

document.addEventListener('DOMContentLoaded', async e => {
    await inicializar();
})