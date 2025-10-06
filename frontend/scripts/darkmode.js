// Seleccionar el body
let body = document.querySelector('body');

// Función para alternar el modo oscuro
function toggleDarkMode() {
    let toggle = document.getElementById('conteiner');
    
    if (localStorage.getItem('darkMode') === 'true') {
        localStorage.setItem('darkMode', 'false');
        toggle.classList.remove('active');
    } else {
        localStorage.setItem('darkMode', 'true');
        toggle.classList.add('active');
    }

    checkDarkMode();
}

// Función para verificar y aplicar el modo oscuro
function checkDarkMode() {
    if (localStorage.getItem('darkMode') === 'true') {
        body.classList.add('active');
    } else {
        body.classList.remove('active');
    }
}

// Función para borrar localStorage
function borrarLH() {
    console.log("andubo ayayaya");
    localStorage.removeItem('darkMode');
    checkDarkMode(); // Actualizar la vista después de borrar
}

    // Si hay un botón toggle, sincronizar su estado visual
    let toggle = document.getElementById('conteiner');
    if (toggle && localStorage.getItem('darkMode') === 'true') {
        toggle.classList.add('active');
    }
