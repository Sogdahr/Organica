<?php
session_start();
require_once "../app/config/database.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

    $mensaje = "Debe introducir un email y una contraseña.";

    } else {

        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario["password_hash"])) {

            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["nombre"] = $usuario["nombre"];
            $_SESSION["email"] = $usuario["email"];

            header("Location: dashboard.php");
            exit;

        } else {
            $mensaje = "Email o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Organica</title>

    <link rel="icon" type="image/png" href="../assets/img/logo-organica.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="pagina-acceso">

<main class="container py-5">

    <section class="hero-corcho mx-auto" style="max-width: 650px;">
        <div class="hero-papel">
            <div class="text-center mb-4">
                <span class="logo-esponja mb-3"></span>
                <h1>Iniciar sesión</h1>
                <p class="texto-bienvenida mx-auto">
                    Accede a tu espacio personal de organización.
                </p>
            </div>

            <?php if (isset($_GET["registro"]) && $_GET["registro"] === "ok"): ?>
                <p class="alert alert-success">Registro completado correctamente. Ya puedes iniciar sesión.</p>
            <?php endif; ?>

            <?php if (!empty($mensaje)): ?>
                <p class="alert alert-danger"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>

                <button type="submit" class="btn btn-organica w-100">
                    Entrar
                </button>

            </form>

            <div class="text-center mt-4">
                <p>¿No tienes cuenta? <a href="registro.php">Crear cuenta</a></p>
                <p><a href="index.php">Volver a la página principal</a></p>
            </div>
        </div>
    </section>

</main>

<footer class="footer-organica">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div>
            <p><strong>Organica</strong> · Proyecto final DAW</p>
            <p class="footer-mini">Aplicación web de productividad personal basada en objetivos, tareas y Pomodoro.</p>
        </div>

        <div class="text-md-end">
            <p class="footer-mini">Desarrollado por Jose Aurelio Rodríguez Belmonte</p>
            <p class="footer-mini">© 2026 Organica</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>