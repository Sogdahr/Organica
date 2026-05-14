<?php
session_start();
require_once "../app/config/database.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION["id_usuario"];

// Total de objetivos del usuario 
$sql = "SELECT COUNT(*) AS total_objetivos
        FROM objetivos
        WHERE id_usuario = ?"; 
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$totalObjetivos = $stmt->fetch(PDO::FETCH_ASSOC)["total_objetivos"];

// Total de tareas pendientes del usuario
$sql = "SELECT COUNT(*) AS tareas_pendientes
        FROM tareas
        INNER JOIN objetivos ON tareas.id_objetivo = objetivos.id_objetivo
        WHERE objetivos.id_usuario = ? AND tareas.estado = 'pendiente'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$tareasPendientes = $stmt->fetch(PDO::FETCH_ASSOC)["tareas_pendientes"];

// Minutos Pomodoro registrados por el usuario
$sql = "SELECT COALESCE(SUM(duracion_minutos), 0) AS minutos_pomodoro
        FROM sesiones_pomodoro
        WHERE id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$minutosPomodoro = $stmt->fetch(PDO::FETCH_ASSOC)["minutos_pomodoro"];

$horasPomodoro = floor($minutosPomodoro / 60);
$minutosRestantes = $minutosPomodoro % 60;


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

    <hr>
    
    <h2>Resumen rápido</h2>

    <div style="display: flex; flex-wrap: wrap; gap: 15px;">

        <div style="border: 1px solid #ccc; padding: 15px; width: 220px;">
            <h3>Objetivos</h3>
            <p><?php echo htmlspecialchars($totalObjetivos); ?> objetivos creados</p>
        </div>

        <div style="border: 1px solid #ccc; padding: 15px; width: 220px;">
            <h3>Tareas pendientes</h3>
            <p><?php echo htmlspecialchars($tareasPendientes); ?> tareas pendientes</p>
        </div>

        <div style="border: 1px solid #ccc; padding: 15px; width: 220px;">
            <h3>Tiempo Pomodoro</h3>
            <p>
                <?php echo htmlspecialchars($horasPomodoro); ?> h
                <?php echo htmlspecialchars($minutosRestantes); ?> min registrados
            </p>
        </div>

    </div>

    <hr>

    <h2>Accesos principales</h2>

    <p>Desde este panel podrás gestionar tus objetivos, tareas y sesiones Pomodoro.</p>

    <p>
        <a href="objetivos.php">Ir a mis objetivos</a>
    </p>

    <p>
        <a href="estadisticas.php">Ver estadísticas personales</a>
    </p>

</body>

</html>