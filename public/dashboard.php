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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Organica</title>

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
                    <li class="nav-item">
                        <a class="nav-link" href="objetivos.php">Mis objetivos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calendario.php">Calendario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="estadisticas.php">Estadísticas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cerrar-sesion" href="logout.php">Cerrar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">

<section class="hero-corcho mb-5">
    <div class="hero-papel">

        <div class="row align-items-center">

            <div class="col-lg-7">
                <span class="etiqueta-seccion">Panel personal</span>

                <h1>Panel principal</h1>

                <p class="texto-bienvenida">
                    Bienvenido/a, 
                    <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong>.
                    Organiza tus objetivos, controla tus tareas y registra tu tiempo de trabajo con Pomodoro.
                </p>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="objetivos.php" class="btn btn-organica">
                        Ir a mis objetivos
                    </a>

                    <a href="calendario.php" class="btn btn-organica-outline">
                        Ver calendario
                    </a>
                </div>
            </div>

            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="zona-postit">

                    <div class="dibujo-maceta">
                        <div class="tallo"></div>
                        <div class="hoja hoja-1"></div>
                        <div class="hoja hoja-2"></div>
                        <div class="maceta"></div>
                    </div>

                    <div class="postit">
                        <span class="chincheta"></span>
                        <p>Un día organizado,<br>una mente en calma.</p>
                        <span class="corazon">♡</span>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>

        <section class="mb-5">
            <h2 class="titulo-seccion">Resumen rápido</h2>

            <div class="row g-4">

                <div class="col-md-4">
                    <article class="tarjeta-resumen">
                        <span class="pin"></span>
                        <div class="icono-resumen">◎</div>
                        <h3>Objetivos</h3>
                        <p class="numero"><?php echo htmlspecialchars($totalObjetivos); ?></p>
                        <p class="descripcion">objetivos creados</p>
                    </article>
                </div>

                <div class="col-md-4">
                    <article class="tarjeta-resumen tarjeta-hoja">
                        <span class="pin"></span>
                        <div class="icono-resumen">☑</div>
                        <h3>Tareas pendientes</h3>
                        <p class="numero"><?php echo htmlspecialchars($tareasPendientes); ?></p>
                        <p class="descripcion">tareas por completar</p>
                    </article>
                </div>

                <div class="col-md-4">
                    <article class="tarjeta-resumen">
                        <span class="pin"></span>
                        <div class="icono-resumen">◷</div>
                        <h3>Tiempo Pomodoro</h3>
                        <p class="numero">
                            <?php echo htmlspecialchars($horasPomodoro); ?> h
                            <?php echo htmlspecialchars($minutosRestantes); ?> min
                        </p>
                        <p class="descripcion">registrados</p>
                    </article>
                </div>

            </div>
        </section>

        <section class="accesos-papel mb-5">
            <h2 class="titulo-seccion mb-4">Accesos principales</h2>

            <div class="row g-3">
                <div class="col-md-4">
                    <a href="objetivos.php" class="acceso-btn acceso-principal">
                        <span>◎</span>
                        Ir a mis objetivos
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="calendario.php" class="acceso-btn">
                        <span>▣</span>
                        Ver calendario
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="estadisticas.php" class="acceso-btn">
                        <span>▥</span>
                        Ver estadísticas
                    </a>
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