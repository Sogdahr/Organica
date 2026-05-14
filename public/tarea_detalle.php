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
$mensaje = "";

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
        $mensaje = "El título de la subtarea no puede estar vacío.";
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
        $mensaje = "El contenido de la nota no puede estar vacío.";
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
        $mensaje = "La duración del Pomodoro no es válida.";
    } else {
        $sql = "INSERT INTO sesiones_pomodoro (id_tarea, id_usuario, duracion_minutos, completada)
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idTarea, $idUsuario, $duracionPomodoro, 1]);

        header("Location: tarea_detalle.php?id_tarea=" . $idTarea);
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
        $mensaje = "El título de la tarea no puede estar vacío.";
    } elseif ($prioridad !== "baja" && $prioridad !== "media" && $prioridad !== "alta") {
        $mensaje = "La prioridad seleccionada no es válida.";
    } elseif ($estado !== "pendiente" && $estado !== "completada") {
        $mensaje = "El estado seleccionado no es válido.";
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
    <title>Detalle de tarea - Organica</title>
</head>

<body>

    <nav>
        <a href="tareas.php?id_objetivo=<?php echo $idObjetivo; ?>">Volver a tareas</a> |
        <a href="objetivo_detalle.php?id_objetivo=<?php echo $idObjetivo; ?>">Volver al objetivo</a> |
        <a href="objetivos.php">Mis objetivos</a> |
        <a href="dashboard.php">Panel principal</a> |
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <hr>

    <h1><?php echo htmlspecialchars($tarea["titulo"]); ?></h1>

    <p>
        <strong>Objetivo:</strong>
        <?php echo htmlspecialchars($tarea["titulo_objetivo"]); ?>
    </p>

    <p>
        <strong>Estado actual:</strong>
        <?php echo htmlspecialchars($tarea["estado"]); ?>
    </p>

    <p>
        <strong>Prioridad:</strong>
        <?php echo htmlspecialchars($tarea["prioridad"]); ?>
    </p>

    <p>
        <strong>Fecha límite:</strong>
        <?php echo htmlspecialchars($tarea["fecha_limite"] ?? "Sin fecha"); ?>
    </p>

    <p>
        <?php echo nl2br(htmlspecialchars($tarea["descripcion"])); ?>
    </p>

    <hr>

    <h2>Editar tarea</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="titulo">Título:</label><br>
        <input 
            type="text" 
            id="titulo" 
            name="titulo" 
            value="<?php echo htmlspecialchars($tarea["titulo"]); ?>"
        ><br><br>

        <label for="descripcion">Descripción:</label><br>
        <textarea 
            id="descripcion" 
            name="descripcion" 
            rows="4" 
            cols="50"
        ><?php echo htmlspecialchars($tarea["descripcion"]); ?></textarea><br><br>

        <label for="fecha_limite">Fecha límite:</label><br>
        <input 
            type="date" 
            id="fecha_limite" 
            name="fecha_limite" 
            value="<?php echo htmlspecialchars($tarea["fecha_limite"] ?? ""); ?>"
        ><br><br>

        <label for="prioridad">Prioridad:</label><br>
        <select id="prioridad" name="prioridad">
            <option value="baja" <?php if ($tarea["prioridad"] === "baja") echo "selected"; ?>>
                Baja
            </option>
            <option value="media" <?php if ($tarea["prioridad"] === "media") echo "selected"; ?>>
                Media
            </option>
            <option value="alta" <?php if ($tarea["prioridad"] === "alta") echo "selected"; ?>>
                Alta
            </option>
        </select><br><br>

        <label for="estado">Estado:</label><br>
        <select id="estado" name="estado">
            <option value="pendiente" <?php if ($tarea["estado"] === "pendiente") echo "selected"; ?>>
                Pendiente
            </option>
            <option value="completada" <?php if ($tarea["estado"] === "completada") echo "selected"; ?>>
                Completada
            </option>
        </select><br><br>

        <button type="submit" name="actualizar_tarea">
            Guardar cambios
        </button>

    </form>

    <hr>

    <h2>Subtareas</h2>

<form method="POST" action="">
    <label for="titulo_subtarea">Nueva subtarea:</label><br>
    <input type="text" id="titulo_subtarea" name="titulo_subtarea">

    <button type="submit" name="crear_subtarea">
        Añadir subtarea
    </button>
</form>

<br>

<?php if (empty($subtareas)): ?>

    <p>Esta tarea todavía no tiene subtareas.</p>

<?php else: ?>

    <ul>
        <?php foreach ($subtareas as $subtarea): ?>

            <li>
                <?php echo htmlspecialchars($subtarea["titulo"]); ?>

                - Estado:
                <strong><?php echo htmlspecialchars($subtarea["estado"]); ?></strong>

                |
                <a href="tarea_detalle.php?id_tarea=<?php echo $idTarea; ?>&cambiar_subtarea=<?php echo $subtarea["id_subtarea"]; ?>">
                    Cambiar estado
                </a>

                |
                <a href="tarea_detalle.php?id_tarea=<?php echo $idTarea; ?>&eliminar_subtarea=<?php echo $subtarea["id_subtarea"]; ?>"
                   onclick="return confirm('¿Seguro que quieres eliminar esta subtarea?');">
                    Eliminar
                </a>
            </li>

        <?php endforeach; ?>
    </ul>

<?php endif; ?>

    <h2>Notas</h2>

<form method="POST" action="">
    <label for="contenido_nota">Nueva nota:</label><br>
    <textarea 
        id="contenido_nota" 
        name="contenido_nota" 
        rows="3" 
        cols="50"
    ></textarea><br><br>

    <button type="submit" name="crear_nota">
        Añadir nota
    </button>
</form>

<br>

<?php if (empty($notas)): ?>

    <p>Esta tarea todavía no tiene notas.</p>

<?php else: ?>

    <?php foreach ($notas as $nota): ?>

        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">

            <p>
                <?php echo nl2br(htmlspecialchars($nota["contenido"])); ?>
            </p>

            <small>
                Creada el:
                <?php echo htmlspecialchars($nota["fecha_creacion"]); ?>
            </small>

            <p>
                <a href="tarea_detalle.php?id_tarea=<?php echo $idTarea; ?>&eliminar_nota=<?php echo $nota["id_nota"]; ?>"
                   onclick="return confirm('¿Seguro que quieres eliminar esta nota?');">
                    Eliminar nota
                </a>
            </p>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

    <h2>Pomodoro</h2>
    <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 15px;">
    <p id="pantalla-tiempo" style="font-size: 32px; font-weight: bold;">
        25:00
    </p>

    <button type="button" id="btn-iniciar">Iniciar</button>
    <button type="button" id="btn-pausar">Pausar</button>
    <button type="button" id="btn-reiniciar">Reiniciar</button>

    <form method="POST" action="" style="margin-top: 15px;">
        <input type="hidden" id="duracion_pomodoro" name="duracion_pomodoro" value="25">

        <button type="submit" name="guardar_pomodoro">
            Guardar sesión Pomodoro
        </button>
    </form>
</div>

<h3>Sesiones Pomodoro registradas</h3>

<?php if (empty($sesionesPomodoro)): ?>

    <p>Todavía no hay sesiones Pomodoro registradas para esta tarea.</p>

<?php else: ?>

    <ul>
        <?php foreach ($sesionesPomodoro as $sesion): ?>
            <li>
                <?php echo htmlspecialchars($sesion["duracion_minutos"]); ?> minutos
                -
                <?php echo htmlspecialchars($sesion["fecha_inicio"]); ?>
            </li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>

    <hr>

    <h2>Zona peligrosa</h2>

    <form method="POST" action="" onsubmit="return confirm('¿Seguro que quieres eliminar esta tarea?');">
        <button type="submit" name="eliminar_tarea">
            Eliminar tarea
        </button>
    </form>

    <script src="../assets/js/pomodoro.js"></script>
</body>

</html>