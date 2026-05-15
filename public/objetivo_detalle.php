<?php 
session_start();
require_once "../app/config/database.php";

    if (!isset($_SESSION["id_usuario"])) {
        header("Location: login.php");
        exit;
    }


    if (!isset($_GET["id_objetivo"])) {
        header("Location: objetivos.php");
        exit;
    }


$idObjetivo = $_GET["id_objetivo"];
$idUsuario = $_SESSION["id_usuario"];
$mensaje = "";

// Comprobación de que el objetivo pertenezca al usuario conectado
$sql = "SELECT * FROM objetivos WHERE id_objetivo = ? AND id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idObjetivo, $idUsuario]);
$objetivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$objetivo) {
        header("Location: objetivos.php");
        exit;
    }

// Contar tareas del objetivo
$sql = "SELECT COUNT(*) AS total_tareas
        FROM tareas
        WHERE id_objetivo = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idObjetivo]);
$totalTareasObjetivo = $stmt->fetch(PDO::FETCH_ASSOC)["total_tareas"];

// Contar tareas completadas del objetivo
$sql = "SELECT COUNT(*) AS tareas_completadas
        FROM tareas
        WHERE id_objetivo = ? AND estado = 'completada'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idObjetivo]);
$tareasCompletadasObjetivo = $stmt->fetch(PDO::FETCH_ASSOC)["tareas_completadas"];

// Contar sesiones Pomodoro del objetivo
$sql = "SELECT COUNT(*) AS total_sesiones,
            COALESCE(SUM(sesiones_pomodoro.duracion_minutos), 0) AS total_minutos
        FROM sesiones_pomodoro
        INNER JOIN tareas ON sesiones_pomodoro.id_tarea = tareas.id_tarea
        WHERE tareas.id_objetivo = ? AND sesiones_pomodoro.id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idObjetivo, $idUsuario]);
$datosPomodoroObjetivo = $stmt->fetch(PDO::FETCH_ASSOC);

$totalSesionesObjetivo = $datosPomodoroObjetivo["total_sesiones"];
$totalMinutosObjetivo = $datosPomodoroObjetivo["total_minutos"];

$horasObjetivo = floor($totalMinutosObjetivo / 60);
$minutosRestantesObjetivo = $totalMinutosObjetivo % 60;

// Obtener tareas del objetivo para mostrarlas en el detalle
$sql = "SELECT id_tarea, titulo, fecha_limite, prioridad, estado
        FROM tareas
        WHERE id_objetivo = ?
        ORDER BY fecha_creacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idObjetivo]);
$tareasObjetivo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Actualizar objetivo
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["actualizar_objetivo"])) {

        $titulo = trim($_POST["titulo"]);
        $descripcion = trim($_POST["descripcion"]);
        $estado = $_POST["estado"];

        if (empty($titulo)) {

            $mensaje = "El título del objetivo no puede estar vacío.";

        } elseif (empty($titulo)) {

            $mensaje = "El estado seleccionado no es válido";

        } else {

            $sql = "UPDATE objetivos
            SET titulo = ?, descripcion = ?, estado = ?
            WHERE id_objetivo = ? AND id_usuario = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $descripcion, $estado, $idObjetivo, $idUsuario]);

            header("Location: objetivo_detalle.php?id_objetivo=" . $idObjetivo);
            exit;

        }
    }


// Eliminar objetivo
    if (isset($_POST["eliminar_objetivo"])) {

        $sql = "DELETE FROM objetivos
                WHERE id_objetivo = ? AND id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idObjetivo, $idUsuario]);

        header("Location: objetivos.php");
        exit;
    }

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del objetivo - Organica</title>

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
                <li class="nav-item"><a class="nav-link" href="estadisticas.php">Estadísticas</a></li>
                <li class="nav-item"><a class="nav-link cerrar-sesion" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">

    <section class="hero-corcho mb-5">
        <div class="hero-papel">
            <span class="etiqueta-seccion">Ficha del objetivo</span>

            <h1><?php echo htmlspecialchars($objetivo["titulo"]); ?></h1>

            <p class="texto-bienvenida">
                <?php echo nl2br(htmlspecialchars($objetivo["descripcion"])); ?>
            </p>

            <div class="mt-4">
                <?php if ($objetivo["estado"] === "completado"): ?>
                    <span class="estado estado-completado">Completado</span>
                <?php else: ?>
                    <span class="estado estado-pendiente">Pendiente</span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="titulo-seccion">Resumen del objetivo</h2>

        <div class="row g-4">

            <div class="col-md-3">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">☑</div>
                    <h3>Tareas</h3>
                    <p class="numero"><?php echo htmlspecialchars($totalTareasObjetivo); ?></p>
                    <p class="descripcion">tareas totales</p>
                </article>
            </div>

            <div class="col-md-3">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">✓</div>
                    <h3>Completadas</h3>
                    <p class="numero"><?php echo htmlspecialchars($tareasCompletadasObjetivo); ?></p>
                    <p class="descripcion">tareas completadas</p>
                </article>
            </div>

            <div class="col-md-3">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">◷</div>
                    <h3>Pomodoros</h3>
                    <p class="numero"><?php echo htmlspecialchars($totalSesionesObjetivo); ?></p>
                    <p class="descripcion">sesiones registradas</p>
                </article>
            </div>

            <div class="col-md-3">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">⌛</div>
                    <h3>Tiempo</h3>
                    <p class="numero">
                        <?php echo htmlspecialchars($horasObjetivo); ?> h
                        <?php echo htmlspecialchars($minutosRestantesObjetivo); ?> min
                    </p>
                    <p class="descripcion">invertidos</p>
                </article>
            </div>

        </div>
    </section>

    <section class="bloque-papel mb-5">
        <h2 class="titulo-seccion">Editar objetivo</h2>

        <?php if (!empty($mensaje)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label for="titulo" class="form-label">Título:</label>
                <input type="text" id="titulo" name="titulo" class="form-control"
                       value="<?php echo htmlspecialchars($objetivo["titulo"]); ?>">
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="form-control"><?php echo htmlspecialchars($objetivo["descripcion"]); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="estado" class="form-label">Estado:</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="pendiente" <?php if ($objetivo["estado"] === "pendiente") echo "selected"; ?>>
                        Pendiente
                    </option>

                    <option value="completado" <?php if ($objetivo["estado"] === "completado") echo "selected"; ?>>
                        Completado
                    </option>
                </select>
            </div>

            <button type="submit" name="actualizar_objetivo" class="btn btn-organica">
                Guardar cambios
            </button>

        </form>
    </section>

    <section class="mb-5">
        <h2 class="titulo-seccion">Tareas del objetivo</h2>

        <?php if (empty($tareasObjetivo)): ?>

            <div class="bloque-papel">
                <p>Este objetivo todavía no tiene tareas creadas.</p>
            </div>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($tareasObjetivo as $tarea): ?>

                    <div class="col-md-4">
                        <article class="tarjeta-tarea h-100">

                            <h3><?php echo htmlspecialchars($tarea["titulo"]); ?></h3>

                            <p>
                                <span class="dato-mini"><?php echo htmlspecialchars($tarea["estado"]); ?></span>
                                <span class="dato-mini"><?php echo htmlspecialchars($tarea["prioridad"]); ?></span>
                            </p>

                            <p class="mt-2">
                                <strong>Fecha límite:</strong>
                                <?php echo htmlspecialchars($tarea["fecha_limite"] ?? "Sin fecha"); ?>
                            </p>

                            <a href="tarea_detalle.php?id_tarea=<?php echo $tarea["id_tarea"]; ?>" class="btn btn-sm btn-organica mt-3">
                                Entrar a la tarea
                            </a>

                        </article>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <div class="mt-4">
            <a href="tareas.php?id_objetivo=<?php echo $objetivo["id_objetivo"]; ?>" class="btn btn-organica">
                Crear o gestionar tareas
            </a>
        </div>
    </section>

    <section class="bloque-papel mb-5">
        <h2 class="titulo-seccion">Eliminar objetivo</h2>

        <p class="mb-3">
            Esta acción eliminará el objetivo y sus tareas asociadas.
        </p>

        <form method="POST" action="" onsubmit="return confirm('¿Seguro que quieres eliminar este objetivo? También se eliminarán sus tareas asociadas.');">
            <button type="submit" name="eliminar_objetivo" class="btn btn-outline-danger">
                Eliminar objetivo
            </button>
        </form>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>