<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../styles/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Crear Espacio</title>
</head>

<body id="body-crear-espacio" class="menues-incluidos">
  <?php include_once __DIR__ . '/../includes/blockSidebarMenu.php' ?>
  <?php include_once __DIR__ . '/../includes/blockTopHeader.php' ?>

  <main id="main-crear-espacio">
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
        <option value="primer piso">Primer piso </option>
        <option value="segundo piso">Segundo Piso </option>


        <input type="submit" value="Registrar Espacio">
    </form>
  </main>

  <script src="../../scripts/espacios_crear.js"></script>
  <script src="../../scripts/menuHamburgesa.js"></script>
  <script src="../../scripts/dropdownMenu.js"></script>
</body>

</html>