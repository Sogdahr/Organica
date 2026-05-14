<?php
session_start();
require_once "../app/config/database.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION["id_usuario"];

// Contar objetivos
$sql = "SELECT COUNT(*) AS total_objetivos
        FROM objetivos
        WHERE id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$totalObjetivos = $stmt->fetch(PDO::FETCH_ASSOC)["total_objetivos"];

// Objetivos completados
$sql = "SELECT COUNT(*) AS objetivos_completados
        FROM objetivos
        WHERE id_usuario = ? AND estado = 'completado'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$objetivosCompletados = $stmt->fetch(PDO::FETCH_ASSOC)["objetivos_completados"];

// Contar tareas
$sql = "SELECT COUNT(*) AS total_tareas
        FROM tareas
        INNER JOIN objetivos ON tareas.id_objetivo = objetivos.id_objetivo
        WHERE objetivos.id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$totalTareas = $stmt->fetch(PDO::FETCH_ASSOC)["total_tareas"];

// Tareas completadas
$sql = "SELECT COUNT(*) AS tareas_completadas
        FROM tareas
        INNER JOIN objetivos ON tareas.id_objetivo = objetivos.id_objetivo
        WHERE objetivos.id_usuario = ? AND tareas.estado = 'completada'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$tareasCompletadas = $stmt->fetch(PDO::FETCH_ASSOC)["tareas_completadas"];

// Contar subtareas
$sql = "SELECT COUNT(*) AS total_subtareas
        FROM subtareas
        INNER JOIN tareas ON subtareas.id_tarea = tareas.id_tarea
        INNER JOIN objetivos ON tareas.id_objetivo = objetivos.id_objetivo
        WHERE objetivos.id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$totalSubtareas = $stmt->fetch(PDO::FETCH_ASSOC)["total_subtareas"];

// Subtareas completadas
$sql = "SELECT COUNT(*) AS subtareas_completadas
        FROM subtareas
        INNER JOIN tareas ON subtareas.id_tarea = tareas.id_tarea
        INNER JOIN objetivos ON tareas.id_objetivo = objetivos.id_objetivo
        WHERE objetivos.id_usuario = ? AND subtareas.estado = 'completada'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$subtareasCompletadas = $stmt->fetch(PDO::FETCH_ASSOC)["subtareas_completadas"];

// Sesiones Pomodoro tiempo
$sql = "SELECT COUNT(*) AS total_sesiones,
        COALESCE(SUM(duracion_minutos), 0) AS total_minutos
        FROM sesiones_pomodoro
        WHERE id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$datosPomodoro = $stmt->fetch(PDO::FETCH_ASSOC);

$totalSesiones = $datosPomodoro["total_sesiones"];
$totalMinutos = $datosPomodoro["total_minutos"];


$porcentajeObjetivos = 0;
if ($totalObjetivos > 0) {
    $porcentajeObjetivos = round(($objetivosCompletados / $totalObjetivos) * 100);
}

$porcentajeTareas = 0;
if ($totalTareas > 0) {
    $porcentajeTareas = round(($tareasCompletadas / $totalTareas) * 100);
}

$porcentajeSubtareas = 0;
if ($totalSubtareas > 0) {
    $porcentajeSubtareas = round(($subtareasCompletadas / $totalSubtareas) * 100);
}

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Estadísticas - Organica</title>
</head>

<body>

        <nav>
            <a href="dashboard.php">Panel principal</a> |
            <a href="objetivos.php">Mis objetivos</a> |
            <a href="calendario.php">Calendario</a> |
            <a href="logout.php">Cerrar sesión</a>
        </nav>
    
    <hr>

    <h1>Estadísticas personales</h1>

    <p>Usuario: <?php echo htmlspecialchars($_SESSION["nombre"]); ?></p>

    <hr>

    <h2>Resumen de objetivos</h2>

    <p>Total de objetivos: <?php echo htmlspecialchars($totalObjetivos); ?></p>
    <p>Objetivos completados: <?php echo htmlspecialchars($objetivosCompletados); ?></p>
    <p>Progreso de objetivos: <?php echo htmlspecialchars($porcentajeObjetivos); ?>%</p>

    <hr>

    <h2>Resumen de tareas</h2>

    <p>Total de tareas: <?php echo htmlspecialchars($totalTareas); ?></p>
    <p>Tareas completadas: <?php echo htmlspecialchars($tareasCompletadas); ?></p>
    <p>Progreso de tareas: <?php echo htmlspecialchars($porcentajeTareas); ?>%</p>

    <hr>

    <h2>Resumen de subtareas</h2>

    <p>Total de subtareas: <?php echo htmlspecialchars($totalSubtareas); ?></p>
    <p>Subtareas completadas: <?php echo htmlspecialchars($subtareasCompletadas); ?></p>
    <p>Progreso de subtareas: <?php echo htmlspecialchars($porcentajeSubtareas); ?>%</p>

    <hr>

    <h2>Productividad Pomodoro</h2>

    <p>Sesiones Pomodoro registradas: <?php echo htmlspecialchars($totalSesiones); ?></p>
    <p>Minutos totales registrados: <?php echo htmlspecialchars($totalMinutos); ?> minutos</p>

</body>

</html>