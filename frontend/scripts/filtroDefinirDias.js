const form_DOM = document.getElementById("formDias");

form_DOM.addEventListener("submit", function (event) {
    event.preventDefault();

    // Inserte filtro aquí
    /* El filtro debe verificar:
        - Se debe seleccionar al menos 1 dia
    */

    form_DOM.submit();
});