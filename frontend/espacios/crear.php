<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Crear Espacio</title>
</head>
<style>
    /*/////////////////////////////////////////////*/
/*             Pagina crear espacios           */
/*/////////////////////////////////////////////*

/* === estilos para crear_espacio === */

#body-crear-espacio {
  font-family: "Poppins", sans-serif;
  background-color: var(--celeste-palido1);
  margin: 0;
  padding: 0;
}

/* Centra el contenido principal */
main {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 60px 20px;
  min-height: calc(100vh - 100px);
}

/* === FORMULARIO === */
#form-crear-espacio {
  background: var(--blanco);
  width: 100%;
  max-width: 600px;
  padding: 40px 30px;
  border-radius: 16px;
  box-shadow: 0 4px 15px var(--sombra);
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* Título */
#form-crear-espacio h1 {
  text-align: center;
  font-size: 1.8rem;
  margin-bottom: 10px;
  color: var(--azul-oscuro);
}

/* Etiquetas */
#form-crear-espacio label {
  font-weight: 600;
  color: var(--gris5);
  margin-bottom: 4px;
}

/* Campos de texto, select y textarea */
#form-crear-espacio input[type="text"],
#form-crear-espacio input[type="number"],
#form-crear-espacio input[type="file"],
#form-crear-espacio select,
#form-crear-espacio textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--gris2);
  border-radius: 8px;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  background-color: var(--blanco);
}

#form-crear-espacio input:focus,
#form-crear-espacio select:focus,
#form-crear-espacio textarea:focus {
  border-color: var(--azul-claro);
  box-shadow: 0 0 5px var(--sombra-azul);
}

/* Botón de enviar */
#form-crear-espacio input[type="submit"] {
  background-color: var(--azul);
  color: var(--blanco);
  font-weight: bold;
  font-size: 1rem;
  padding: 12px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.1s ease;
  margin-top: 10px;
}

#form-crear-espacio input[type="submit"]:hover {
  background-color: var(--azul-oscuro);
  transform: translateY(-1px);
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
  #form-crear-espacio {
    padding: 30px 20px;
  }

  #form-crear-espacio h1 {
    font-size: 1.5rem;
  }
}
</style>

<body id="body-crear-espacio">

    <main>
        <form id="form-crear-espacio">
            <h1>Crear Espacio</h1>

            <label for="tipo">Tipo de Espacio</label>
            <select name="tipo" id="tipo" required>
                <option value="">Seleccionar tipo...</option>
                <option value="aula">Aula infromatica</option>
                <option value="laboratorio">Laboratorio de quimica</option>
                <option value="laboratorio">Laboratorio de robotica</option>
                <option value="laboratorio">Laboratorio de electricidad</option>
                <option value="taller">Taller de mantenimiento </option>
                <option value="oficina">Oficina</option>
                <option value="otro">Otro</option>
            </select>

            <label for="capacidad">Capacidad</label>
            <input type="number" name="capacidad" id="capacidad" min="1" max="500" placeholder="Ej: 30" required>

            <label for="tipo">Ubicacion </label>
            <select name="ubicacion" id="ubicacion" required>
                <option value="">Seleccionar ubicación...</option>
                <option value="Planta baja">Planta baja</option>
                <option value="primer piso" >Primer piso </option>
                <option value="segundo piso" >Segundo Piso </option>
        

            <input type="submit" value="Registrar Espacio">
        </form>
    </main>

    <script src="../../scripts/espacios_crear.js"></script>
    <script src="../../scripts/menuHamburgesa.js"></script>
    <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>
