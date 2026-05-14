<?php
require_once "../app/config/database.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($nombre) || empty($email) || empty($password)) {

        $mensaje = "Todos los campos son obligatorios.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "Introduzca un formato válido para el email.";

    } elseif (strlen($password) < 8) {

        $mensaje = "La contraseña debe tener al menos 8 caracteres.";

    } else {

        $sql = "SELECT id_usuario FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuarioExistente = $stmt->fetch();

            if ($usuarioExistente) {

                $mensaje = "Este email ya esta registrado.";

            } else {

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $email, $passwordHash]);

                header("Location: login.php?registro=ok");
                exit;
            }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro - Organica</title>
</head>

<body>

    <h1>Crear cuenta</h1>

    <?php if (!empty($mensaje)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre"><br><br>

        <label for="email">Correo electrónico:</label><br>
        <input type="email" id="email" name="email"><br><br>

        <label for="password">Contraseña:</label><br>
        <input type="password" id="password" name="password"><br><br>

        <button type="submit">Registrarse</button>

    </form>

    <p>¿Tienes ya una cuenta creada? <a href="login.php"></a>Inicia sesión</p>
    
</body>
</html>