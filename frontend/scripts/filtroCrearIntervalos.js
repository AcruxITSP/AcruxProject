const form_DOM = document.getElementById("formIntervalos");

form_DOM.addEventListener("submit", function (event) {
    event.preventDefault();

    // Inserte filtro aquí
    /* El filtro debe verificar:
        - La hora de inicio no puede ser mayor ni igual a la hora de salida
        - 0 <= hora <= 23  y  0 <= minutos <= 59
        - No puede haber menos de una clase en el dia
        - Una clase no puede durar menos de 10 minutos

        También debe:
        - Verificar que el dia se pueda dividir entre el tiempo de clase + recreo.
          formula: resto = (varTiempo + recreo) % (claseDur + recreo)
          En caso de que no sea divisible, darle la opcion al usuario de quitar el resto
    */

    form_DOM.submit();
});