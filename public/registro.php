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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Organica</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<main class="container py-5">

    <section class="hero-corcho mx-auto" style="max-width: 700px;">
        <div class="hero-papel">
            <div class="text-center mb-4">
                <span class="logo-esponja mb-3"></span>
                <h1>Crear cuenta</h1>
                <p class="texto-bienvenida mx-auto">
                    Crea tu espacio personal para organizar objetivos, tareas y sesiones Pomodoro.
                </p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <p class="alert alert-danger"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>

                <button type="submit" class="btn btn-organica w-100">
                    Registrarse
                </button>

            </form>

            <div class="text-center mt-4">
                <p>¿Tienes ya una cuenta creada? <a href="login.php">Inicia sesión</a></p>
                <p><a href="index.php">Volver a la página principal</a></p>
            </div>
        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>