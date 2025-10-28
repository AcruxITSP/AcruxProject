<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <title>Funcionarios ITSP</title>

  <style>
    :root {
      --celeste-palido1: #f6f7fb;
      --celeste-palido2: #dfe9f3;
      --azul-claro: #0056b3;
      --azul: #14418e;
      --azul-grisaceo: #343a80;
      --azul-oscuro: #0f316d;
      --azul-oscuro2: #1f2357;
      --verde-claro: #5bbd5b;
      --verde: #4cae4c;
      --coral: #dc3545;
      --coral2: #c72333;
      --mostaza: #ae8300;
      --blanco: #ffffff;
      --gris1: #dddddd;
      --gris2: #cccccc;
      --gris3: #aaaaaa;
      --gris4: #777777;
      --gris5: #333333;
      --negro: #000000;
      --sombra: rgba(0, 0, 0, 0.15);
      --sombra-azul: rgba(0, 123, 255, 0.25);
    }

    html,
    body {
      height: 100%;
      margin: 0;
    }

    #body-ver-funcionarios {
      font-family: "Segoe UI", Roboto, sans-serif;
      display: flex;
      justify-content: center;
      padding: 2rem;
      box-sizing: border-box;
      min-height: 100vh;
    }

    main {
      width: 100%;
      max-width: 1100px;
      box-sizing: border-box;
      position: relative;
    }

    /* === HEADER === */
    .page-header {
      position: sticky;
      top: 0;
      z-index: 50;
      margin: 0 0 1rem 0;
      padding: 0.8rem 0;
      border-radius: 8px;
      backdrop-filter: blur(4px);
    }

    .page-header h1 {
      margin: 0;
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--azul-oscuro);
    }

    /* === GRID === */
    .contenedor-funcionarios {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
      background-color: var(--celeste-palido2);
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 2px 8px var(--sombra);
      box-sizing: border-box;
    }

    /* === TARJETAS === */
    .funcionario {
      background-color: var(--blanco);
      border: 1px solid var(--gris2);
      border-radius: 10px;
      padding: 1.2rem;
      display: flex;
      flex-direction: column;
      gap: 0.6rem;
      box-shadow: 0 1px 3px var(--sombra);
      transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
      min-height: 120px;
    }

    .funcionario:hover {
      border-color: var(--azul-oscuro);
      box-shadow: 0 3px 8px var(--sombra-azul);
      transform: translateY(-2px);
    }

    .desc p:first-child {
      font-weight: bold;
      color: var(--azul-oscuro2);
      font-size: 1.05rem;
      margin: 0 0 0.15rem 0;
    }

    .desc p:last-child {
      color: var(--gris4);
      font-size: 0.95rem;
      margin: 0;
    }

    .info-funcionario {
      list-style-type: none;
      padding-left: 0;
      margin: 0.5rem 0;
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
      font-size: 0.95rem;
    }

    .info-funcionario li {
      background: var(--celeste-palido1);
      padding: 0.28rem 0.6rem;
      border-radius: 4px;
      margin: 0;
    }

    /* === BOTONES DE LAS TARJETAS === */
    .funcionario .botones {
      display: flex;
      justify-content: flex-end;
      gap: 0.6rem;
      border-top: 1px solid var(--gris1);
      padding-top: 0.6rem;
      margin-top: auto;
    }

    .funcionario .botones button {
      border: none;
      border-radius: 6px;
      padding: 0.45rem 0.8rem;
      cursor: pointer;
      font-weight: 600;
      color: var(--blanco);
      transition: transform 0.1s ease, opacity 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
    }

    .funcionario .botones .borrar {
      background-color: var(--coral2);
    }

    .funcionario .botones .editar {
      background-color: var(--mostaza);
    }

    .funcionario .botones button:hover {
      transform: scale(1.05);
      opacity: 0.9;
    }

    /* === BOTONES FLOTANTES ABAJO DERECHA === */
    .botones-flotantes {
      position: fixed;
      bottom: 25px;
      right: 25px;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 12px;
      z-index: 100;
    }

    .botones-flotantes button {
      background-color: var(--azul-oscuro);
      color: var(--blanco);
      border: none;
      border-radius: 10px;
      padding: 0.8rem 1.2rem;
      font-size: 1rem;
      font-weight: 600;
      box-shadow: 0 4px 8px var(--sombra);
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
    }

    @media (max-width: 900px) {
      .contenedor-funcionarios {
        grid-template-columns: 1fr;
      }

      .botones-flotantes {
        bottom: 15px;
        right: 15px;
      }
    }
  </style>
</head>

<body id="body-ver-funcionarios">
  <main>
    <header class="page-header">
      <h1>Funcionarios ITSP</h1>
    </header>

    <div class="contenedor-funcionarios">
      <div class="funcionario">
        <div class="desc">
          <p class="nombre-funcionario">Facundo Rubil</p>
          <p>Docente de Programación</p>
        </div>
        <ul class="info-funcionario">
          <li>Cédula: 11111111</li>
          <li>Correo: facundo.rubil@itsp.edu.uy</li>
        </ul>
        <div class="botones">
          <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
          <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
        </div>
      </div>

      <div class="funcionario">
        <div class="desc">
          <p class="nombre-funcionario">Franco Povea</p>
          <p>Profesor de Sistemas Operativos</p>
        </div>
        <ul class="info-funcionario">
          <li>Cédula: 22222222</li>
          <li>Correo: franco.povea@itsp.edu.uy</li>
        </ul>
        <div class="botones">
          <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
          <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
        </div>
      </div>

      <div class="funcionario">
        <div class="desc">
          <p class="nombre-funcionario">Lucía Fernández</p>
          <p>Personal de Limpieza</p>
        </div>
        <ul class="info-funcionario">
          <li>Cédula: 33333333</li>
          <li>Correo: lucia.fernandez@itsp.edu.uy</li>
        </ul>
        <div class="botones">
          <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
          <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
        </div>
      </div>

      <div class="funcionario">
        <div class="desc">
          <p class="nombre-funcionario">Carlos Gómez</p>
          <p>Personal de Limpieza</p>
        </div>
        <ul class="info-funcionario">
          <li>Cédula: 44444444</li>
          <li>Correo: carlos.gomez@itsp.edu.uy</li>
        </ul>
        <div class="botones">
          <button class="borrar" title="Borrar"><i class="bi bi-trash-fill"></i></button>
          <button class="editar" title="Editar"><i class="bi bi-pencil"></i></button>
        </div>
      </div>
    </div>

    <!-- BOTONES FLOTANTES -->
    <div class="botones-flotantes">
      <button onclick="location.href='#'"><i class="bi bi-person-plus"></i> Crear funcionario</button>
    </div>
  </main>
</body>

</html>
