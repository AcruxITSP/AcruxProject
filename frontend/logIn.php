<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="styles/styles.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<body class="login-page">

    <div class="login-container">
    <!-- Formulario -->
    <div class="login-form">
        <h2>¡Hola!</h2>
        <form id="logInForm" action="scriptLogIn.php" method="post">
            <div class="input-group">
                <i class="fa fa-id-card"></i>
                <input type="text" name="DNI" placeholder="Cédula" required>
            </div>
            <div class="input-group">
    <i class="fa fa-lock"></i>
    <input type="password" name="password" placeholder="Contraseña" required>
</div>
<p class="password-info">
    La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y símbolos.
</p>

            <input id="btn-submit" type="submit" value="Ingresar">
      </form>
    </div>

    <!-- Información lateral -->
    <div class="login-info">
        <h3>¡Bienvenido de nuevo!</h3>
        <p>Ingresa tus datos para acceder a tu cuenta.</p>
    </div>
</div>

    <script src="scripts/filtroLogin.js"></script>
</body>
</html>
