<?php 
session_start();
require_once "../app/config/database.php";

    if (!isset($_SESSION["id_usuario"])) {
        header("Location: login.php");
        exit;
    }

    if (!isset($_GET["id_tarea"])) {
        header("Location: objetivos.php");
        exit;
    }

$idUsuario = $_SESSION["id_usuario"];
$idTarea = $_GET["id_tarea"];

$mensajeTarea = "";
$mensajeSubtarea = "";
$mensajeNota = "";
$mensajePomodoro = "";

// Obtener tarea y comprobación de seguridad
$sql = "SELECT tareas.*, objetivos.id_usuario, objetivos.titulo AS titulo_objetivo
        FROM tareas
        INNER JOIN objetivos ON tareas.id_objetivo = objetivos.id_objetivo
        WHERE tareas.id_tarea = ? AND objetivos.id_usuario = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$idTarea, $idUsuario]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarea) {
        header("Location: objetivos.php");
        exit;
    }

$idObjetivo = $tarea["id_objetivo"];

// Crear subtarea
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_subtarea"])) {
    $tituloSubtarea = trim($_POST["titulo_subtarea"]);

    if (empty($tituloSubtarea)) {
        $mensajeSubtarea = "El título de la subtarea no puede estar vacío.";
    } else {
        $sql = "INSERT INTO subtareas (id_tarea, titulo)
                VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idTarea, $tituloSubtarea]);

        header("Location: tarea_detalle.php?id_tarea=" . $idTarea);
        exit;
    }
}

// Crear nota
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_nota"])) {
    $contenidoNota = trim($_POST["contenido_nota"]);

    if (empty($contenidoNota)) {
        $mensajeNota = "El contenido de la nota no puede estar vacío.";
    } else {
        $sql = "INSERT INTO notas (id_tarea, id_usuario, contenido)
                VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idTarea, $idUsuario, $contenidoNota]);

        header("Location: tarea_detalle.php?id_tarea=" . $idTarea);
        exit;
    }
}

// Guardar sesión Pomodoro
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardar_pomodoro"])) {
    $duracionPomodoro = $_POST["duracion_pomodoro"];

    if (!is_numeric($duracionPomodoro) || $duracionPomodoro <= 0) {
        $mensajePomodoro = "Debes iniciar el Pomodoro antes de guardar una sesión.";
    } else {
        $sql = "INSERT INTO sesiones_pomodoro (id_tarea, id_usuario, duracion_minutos, completada)
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idTarea, $idUsuario, $duracionPomodoro, 1]);

        header("Location: tarea_detalle.php?id_tarea=" . $idTarea . "#pomodoro");
        exit;
    }
}


// Cambiar estado de subtarea
if (isset($_GET["cambiar_subtarea"])) {
    $idSubtarea = $_GET["cambiar_subtarea"];

    $sql = "SELECT * FROM subtareas 
            WHERE id_subtarea = ? AND id_tarea = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idSubtarea, $idTarea]);
    $subtarea = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subtarea) {
        $nuevoEstado = ($subtarea["estado"] === "pendiente") ? "completada" : "pendiente";

        $sql = "UPDATE subtareas 
                SET estado = ? 
                WHERE id_subtarea = ? AND id_tarea = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevoEstado, $idSubtarea, $idTarea]);
    }

    header("Location: tarea_detalle.php?id_tarea=" . $idTarea);
    exit;
}


// Eliminar subtarea
if (isset($_GET["eliminar_subtarea"])) {
    $idSubtarea = $_GET["eliminar_subtarea"];

    $sql = "DELETE FROM subtareas 
            WHERE id_subtarea = ? AND id_tarea = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idSubtarea, $idTarea]);

    header("Location: tarea_detalle.php?id_tarea=" . $idTarea);
    exit;
}

// Eliminar nota
if (isset($_GET["eliminar_nota"])) {
    $idNota = $_GET["eliminar_nota"];

    $sql = "DELETE FROM notas 
            WHERE id_nota = ? AND id_tarea = ? AND id_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idNota, $idTarea, $idUsuario]);

    header("Location: tarea_detalle.php?id_tarea=" . $idTarea);
    exit;
}

// Actualizar tarea
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["actualizar_tarea"])) {
    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $fechaLimite = $_POST["fecha_limite"];
    $prioridad = $_POST["prioridad"];
    $estado = $_POST["estado"];

    if (empty($titulo)) {
        $mensajeTarea = "El título de la tarea no puede estar vacío.";
    } elseif ($prioridad !== "baja" && $prioridad !== "media" && $prioridad !== "alta") {
        $mensajeTarea = "La prioridad seleccionada no es válida.";
    } elseif ($estado !== "pendiente" && $estado !== "completada") {
        $mensajeTarea = "El estado seleccionado no es válido.";
    } else {
        if (empty($fechaLimite)) {
            $fechaLimite = null;
        }

        $sql = "UPDATE tareas
                SET titulo = ?, descripcion = ?, fecha_limite = ?, prioridad = ?, estado = ?
                WHERE id_tarea = ? AND id_objetivo = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $descripcion, $fechaLimite, $prioridad, $estado, $idTarea, $idObjetivo]);

        header("Location: tarea_detalle.php?id_tarea=" . $idTarea);
        exit;
    }
}

// Eliminar tarea
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["eliminar_tarea"])) {
    $sql = "DELETE FROM tareas
            WHERE id_tarea = ? AND id_objetivo = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idTarea, $idObjetivo]);

    header("Location: tareas.php?id_objetivo=" . $idObjetivo);
    exit;
}


// Obtener subtareas de la tarea
    $sql = "SELECT * FROM subtareas 
            WHERE id_tarea = ? 
    ORDER BY id_subtarea ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idTarea]);
    $subtareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener notas de la tarea
    $sql = "SELECT * FROM notas 
        WHERE id_tarea = ? AND id_usuario = ?
        ORDER BY fecha_creacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idTarea, $idUsuario]);
    $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener sesiones Pomodoro de la tarea
$sql = "SELECT * FROM sesiones_pomodoro 
        WHERE id_tarea = ? AND id_usuario = ?
        ORDER BY fecha_inicio DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idTarea, $idUsuario]);
$sesionesPomodoro = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de tarea - Organica</title>

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
                <li class="nav-item"><a class="nav-link" href="tareas.php?id_objetivo=<?php echo $idObjetivo; ?>">Volver a tareas</a></li>
                <li class="nav-item"><a class="nav-link cerrar-sesion" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">

    <section class="hero-corcho mb-5">
        <div class="hero-papel">
            <span class="etiqueta-seccion">Detalle de tarea</span>

            <h1><?php echo htmlspecialchars($tarea["titulo"]); ?></h1>

            <p class="texto-bienvenida">
                Objetivo: <strong><?php echo htmlspecialchars($tarea["titulo_objetivo"]); ?></strong>
            </p>

            <div class="mt-4">
                <span class="dato-mini"><?php echo htmlspecialchars($tarea["estado"]); ?></span>
                <span class="dato-mini"><?php echo htmlspecialchars($tarea["prioridad"]); ?></span>
                <span class="dato-mini">
                    Fecha límite:
                    <?php echo htmlspecialchars($tarea["fecha_limite"] ?? "Sin fecha"); ?>
                </span>
            </div>

            <p class="mt-4">
                <?php echo nl2br(htmlspecialchars($tarea["descripcion"])); ?>
            </p>
        </div>
    </section>

    <section class="bloque-papel mb-5">
        <h2 class="titulo-seccion">Editar tarea</h2>

        <?php if (!empty($mensajeTarea)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($mensajeTarea); ?></p>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label for="titulo" class="form-label">Título:</label>
                <input type="text" id="titulo" name="titulo" class="form-control"
                       value="<?php echo htmlspecialchars($tarea["titulo"]); ?>">
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="form-control"><?php echo htmlspecialchars($tarea["descripcion"]); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="fecha_limite" class="form-label">Fecha límite:</label>
                    <input type="date" id="fecha_limite" name="fecha_limite" class="form-control"
                           value="<?php echo htmlspecialchars($tarea["fecha_limite"] ?? ""); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="prioridad" class="form-label">Prioridad:</label>
                    <select id="prioridad" name="prioridad" class="form-select">
                        <option value="baja" <?php if ($tarea["prioridad"] === "baja") echo "selected"; ?>>
                            Baja
                        </option>
                        <option value="media" <?php if ($tarea["prioridad"] === "media") echo "selected"; ?>>
                            Media
                        </option>
                        <option value="alta" <?php if ($tarea["prioridad"] === "alta") echo "selected"; ?>>
                            Alta
                        </option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="estado" class="form-label">Estado:</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="pendiente" <?php if ($tarea["estado"] === "pendiente") echo "selected"; ?>>
                            Pendiente
                        </option>
                        <option value="completada" <?php if ($tarea["estado"] === "completada") echo "selected"; ?>>
                            Completada
                        </option>
                    </select>
                </div>
            </div>

            <button type="submit" name="actualizar_tarea" class="btn btn-organica">
                Guardar cambios
            </button>

        </form>
    </section>

    <div class="row g-4 mb-5">

        <div class="col-lg-6">
            <section class="bloque-papel h-100">
                <h2 class="titulo-seccion">Subtareas</h2>

                <?php if (!empty($mensajeSubtarea)): ?>
                    <p class="alert alert-danger"><?php echo htmlspecialchars($mensajeSubtarea); ?></p>
                <?php endif; ?>

                <form method="POST" action="" class="mb-4">
                    <label for="titulo_subtarea" class="form-label">Nueva subtarea:</label>

                    <div class="d-flex gap-2">
                        <input type="text" id="titulo_subtarea" name="titulo_subtarea" class="form-control">

                        <button type="submit" name="crear_subtarea" class="btn btn-organica">
                            Añadir
                        </button>
                    </div>
                </form>

                <?php if (empty($subtareas)): ?>

                    <p>Esta tarea todavía no tiene subtareas.</p>

                <?php else: ?>

                    <ul class="list-group">

                        <?php foreach ($subtareas as $subtarea): ?>

                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($subtarea["titulo"]); ?></strong>
                                    <br>
                                    <span class="dato-mini"><?php echo htmlspecialchars($subtarea["estado"]); ?></span>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="tarea_detalle.php?id_tarea=<?php echo $idTarea; ?>&cambiar_subtarea=<?php echo $subtarea["id_subtarea"]; ?>" class="btn btn-sm btn-organica-outline">
                                        Cambiar
                                    </a>

                                    <a href="tarea_detalle.php?id_tarea=<?php echo $idTarea; ?>&eliminar_subtarea=<?php echo $subtarea["id_subtarea"]; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('¿Seguro que quieres eliminar esta subtarea?');">
                                        Eliminar
                                    </a>
                                </div>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                <?php endif; ?>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="bloque-papel h-100">
                <h2 class="titulo-seccion">Notas</h2>

                <?php if (!empty($mensajeNota)): ?>
                    <p class="alert alert-danger"><?php echo htmlspecialchars($mensajeNota); ?></p>
                <?php endif; ?>

                <form method="POST" action="" class="mb-4">
                    <label for="contenido_nota" class="form-label">Nueva nota:</label>

                    <textarea id="contenido_nota" name="contenido_nota" rows="3" class="form-control mb-3"></textarea>

                    <button type="submit" name="crear_nota" class="btn btn-organica">
                        Añadir nota
                    </button>
                </form>

                <?php if (empty($notas)): ?>

                    <p>Esta tarea todavía no tiene notas.</p>

                <?php else: ?>

                    <div class="row g-3">

                        <?php foreach ($notas as $nota): ?>

                            <div class="col-md-6">
                                <div class="nota-postit h-100">
                                    <p>
                                        <?php echo nl2br(htmlspecialchars($nota["contenido"])); ?>
                                    </p>

                                    <small>
                                        Creada el:
                                        <?php echo htmlspecialchars($nota["fecha_creacion"]); ?>
                                    </small>

                                    <p class="mt-3">
                                        <a href="tarea_detalle.php?id_tarea=<?php echo $idTarea; ?>&eliminar_nota=<?php echo $nota["id_nota"]; ?>"
                                           onclick="return confirm('¿Seguro que quieres eliminar esta nota?');">
                                            Eliminar nota
                                        </a>
                                    </p>
                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>
            </section>
        </div>

    </div>

    <section class="pomodoro-panel mb-5" id="pomodoro">
        <h2 class="titulo-seccion">Pomodoro</h2>

        <?php if (!empty($mensajePomodoro)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($mensajePomodoro); ?></p>
        <?php endif; ?>

        <p id="pantalla-tiempo" class="pantalla-pomodoro">
            25:00
        </p>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" id="btn-iniciar" class="btn btn-organica">Iniciar</button>
            <button type="button" id="btn-pausar" class="btn btn-organica-outline">Pausar</button>
            <button type="button" id="btn-reiniciar" class="btn btn-outline-secondary">Reiniciar</button>
        </div>

        <form method="POST" action="" id="form_pomodoro" class="mb-4">
            <input type="hidden" id="duracion_pomodoro" name="duracion_pomodoro" value="0">

            <button type="submit" name="guardar_pomodoro" class="btn btn-organica">
                Guardar sesión Pomodoro
            </button>
        </form>

        <h3>Sesiones Pomodoro registradas</h3>

        <?php if (empty($sesionesPomodoro)): ?>

            <p>Todavía no hay sesiones Pomodoro registradas para esta tarea.</p>

        <?php else: ?>

            <ul class="list-group">

                <?php foreach ($sesionesPomodoro as $sesion): ?>

                    <li class="list-group-item">
                        <?php echo htmlspecialchars($sesion["duracion_minutos"]); ?> minutos
                        -
                        <?php echo htmlspecialchars($sesion["fecha_inicio"]); ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        <?php endif; ?>
    </section>

    <section class="bloque-papel mb-5">
        <h2 class="titulo-seccion">Eliminar tarea</h2>

        <p class="mb-3">
            Esta acción eliminará la tarea y sus subtareas, notas y sesiones asociadas.
        </p>

        <form method="POST" action="" onsubmit="return confirm('¿Seguro que quieres eliminar esta tarea?');">
            <button type="submit" name="eliminar_tarea" class="btn btn-outline-danger">
                Eliminar tarea
            </button>
        </form>
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
<script src="../assets/js/pomodoro.js"></script>

</body>
</html>