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
    <title>Login - Organica</title>
</head>

<body>

    <h1>Iniciar sesión</h1>

    <?php if (isset($_GET["registro"]) && $_GET["registro"] === "ok"): ?>
        <p style="color: green;">Registro completado correctamente. Ya puedes iniciar sesión.</p>
    <?php endif; ?>

    <?php if (!empty($mensaje)): ?>
        <p style="color: red;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="email">Correo electrónico:</label><br>
        <input type="email" id="email" name="email"><br><br>

        <label for="password">Contraseña:</label><br>
        <input type="password" id="password" name="password"><br><br>

        <button type="submit">Entrar</button>

    </form>

    <p>¿No tienes cuenta <a href="registro.php">Crear cuenta</a></p>
    <p><a href="index.php">Volver a la página principal</a></p>
    
</body>

</html>