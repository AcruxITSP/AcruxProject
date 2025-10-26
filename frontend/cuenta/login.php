<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require dirname(__FILE__).'/../includes/header.php' ?>
    <link rel="stylesheet" href="../styles/styles.css">
    <title>Document</title>
</head>
<body id="body-login">
    <form id="form-login">
        <h2>Log In</h2>
        <input name="ci" type="text" minlength="8" maxlength="8" placeholder="Nombre de usuario" required>
        <input name="password" type="password" placeholder="Contraseña" required>
        <input type="submit" value="Login">
    </form>

    <script src="../scripts/cuenta_login.js"></script>
</body>
</html>