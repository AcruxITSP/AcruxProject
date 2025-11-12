function addEvenListenersCheckboxes(label_opciones) {

    const opciones = document.querySelectorAll(`#${label_opciones.id} + .opcionesCheckboxes input[type='checkbox']`);
    const placeholder = label_opciones.querySelector(".placeholder");

    opciones.forEach(opcion => {

        opcion.addEventListener('change', () => {
            let customValue = String(opcion.getAttribute(`registerName`));

            if (opcion.checked) {

                const p = document.createElement("p");
                p.innerText = `${customValue}`;
                p.setAttribute('content', `${customValue}`);
                label_opciones.appendChild(p);

            } else {
                const p_remove = document.querySelector(`p[content='${customValue}']`);
                label_opciones.removeChild(p_remove);
            }

            placeholder.remove();
        });

    });
}