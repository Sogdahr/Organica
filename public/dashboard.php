<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Organica</title>
</head>

<body>

    <h1>Panel principal</h1>

    <p>Bienvenid@, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></p>

    <nav>

        <a href="objetivos.php">Mis objetivos</a> |
        <a href="estadisticas.php">Estadisticas</a> |
        <a href="logout.php">Cerrar sesión</a>

    </nav>
    
    <h2>Resumen</h2>
    <p>Desde este panel podrás gestionar tus objetivos, tareas y sesiones Pomodoro.</p>

</body>

</html>