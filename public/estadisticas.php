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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Organica</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<nav class="navbar navbar-expand-lg organica-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
            <span class="logo-esponja"></span>
            <span>Organica</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Panel principal</a></li>
                <li class="nav-item"><a class="nav-link" href="objetivos.php">Mis objetivos</a></li>
                <li class="nav-item"><a class="nav-link" href="calendario.php">Calendario</a></li>
                <li class="nav-item"><a class="nav-link cerrar-sesion" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">

    <section class="hero-corcho mb-5">
        <div class="hero-papel">
            <span class="etiqueta-seccion">Datos de productividad</span>

            <h1>Estadísticas personales</h1>

            <p class="texto-bienvenida">
                Resumen general de objetivos, tareas, subtareas y sesiones Pomodoro del usuario
                <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong>.
            </p>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="titulo-seccion">Resumen general</h2>

        <div class="row g-4">

            <div class="col-md-4">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">◎</div>
                    <h3>Objetivos</h3>
                    <p class="numero"><?php echo htmlspecialchars($totalObjetivos); ?></p>
                    <p class="descripcion"><?php echo htmlspecialchars($objetivosCompletados); ?> completados</p>

                    <div class="barra-progreso">
                        <div style="width: <?php echo htmlspecialchars($porcentajeObjetivos); ?>%;"></div>
                    </div>

                    <p class="descripcion"><?php echo htmlspecialchars($porcentajeObjetivos); ?>% de progreso</p>
                </article>
            </div>

            <div class="col-md-4">
                <article class="tarjeta-resumen h-100 tarjeta-hoja">
                    <span class="pin"></span>
                    <div class="icono-resumen">☑</div>
                    <h3>Tareas</h3>
                    <p class="numero"><?php echo htmlspecialchars($totalTareas); ?></p>
                    <p class="descripcion"><?php echo htmlspecialchars($tareasCompletadas); ?> completadas</p>

                    <div class="barra-progreso">
                        <div style="width: <?php echo htmlspecialchars($porcentajeTareas); ?>%;"></div>
                    </div>

                    <p class="descripcion"><?php echo htmlspecialchars($porcentajeTareas); ?>% de progreso</p>
                </article>
            </div>

            <div class="col-md-4">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">✓</div>
                    <h3>Subtareas</h3>
                    <p class="numero"><?php echo htmlspecialchars($totalSubtareas); ?></p>
                    <p class="descripcion"><?php echo htmlspecialchars($subtareasCompletadas); ?> completadas</p>

                    <div class="barra-progreso">
                        <div style="width: <?php echo htmlspecialchars($porcentajeSubtareas); ?>%;"></div>
                    </div>

                    <p class="descripcion"><?php echo htmlspecialchars($porcentajeSubtareas); ?>% de progreso</p>
                </article>
            </div>

        </div>
    </section>

    <section class="accesos-papel">
        <h2 class="titulo-seccion">Productividad Pomodoro</h2>

        <div class="row g-4">

            <div class="col-md-6">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">◷</div>
                    <h3>Sesiones registradas</h3>
                    <p class="numero"><?php echo htmlspecialchars($totalSesiones); ?></p>
                    <p class="descripcion">sesiones Pomodoro guardadas</p>
                </article>
            </div>

            <div class="col-md-6">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">⌛</div>
                    <h3>Tiempo total</h3>
                    <p class="numero"><?php echo htmlspecialchars($totalMinutos); ?> min</p>
                    <p class="descripcion">minutos registrados</p>
                </article>
            </div>

        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>