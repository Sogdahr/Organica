<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - Organica</title>
</head>

<body>

    <h1>Iniciar sesión</h1>

    <?php if (isset($_GET["registro"]) && $_GET["registro"] === "ok"): ?>
        <p style="color: green;">Registro completado correctamente. Ya puedes iniciar sesión.</p>
    <?php endif; ?>

    <p>Formulario de login pendiente de implementar.</p>

    <p><a href="registro.php">Crear cuenta</a></p>
    <p><a href="index.php">Volver a la página principal</a></p>
    
</body>

</html>