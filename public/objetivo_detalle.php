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
    <title>Detalle del objetivo - Organica</title>
</head>

<body>

    <nav>
        <a href="dashboard.php">Panel principal</a>
        <a href="objetivos.php">Mis objetivos</a>
        <a href="calendario.php">Calendario</a>
        <a href="estadisticas.php">Estadísticas</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <hr>

    <h1><?php echo htmlspecialchars($objetivo["titulo"]); ?></h1>

    <p>
        <strong>Estado actual:</strong>
        <?php echo htmlspecialchars($objetivo["estado"]); ?>
    </p>

    <p><?php echo nl2br(htmlspecialchars($objetivo["descripcion"])); ?></p>
    

    <hr>

    <h2>Editar objetivo</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form action="" method="POST">
            <label for="titulo">Título:</label><br>
            <input
                type="text"
                id="titulo"
                name="titulo"
                value="<?php echo htmlspecialchars($objetivo["titulo"]); ?>"><br><br>

        <label for="descripcion">Descripción</label><br>
        <textarea
                id="descripcion"
                name="descripcion"
                rows="4"
                cols="50"
        ><?php echo htmlspecialchars($objetivo["descripcion"]); ?></textarea><br><br>

        <label for="estado">Estado:</label><br>
        <select name="estado" id="estado">

            <option value="pendiente" <?php if ($objetivo["estado"] === "pendiente") echo "selected"; ?>>
                Pendiente
            </option>

            <option value="completado" <?php if ($objetivo["estado"] === "completado") echo "selected"; ?>>
                Completado
            </option>

        </select><br><br>

        <button type="submit" name="actualizar_objetivo">
        Guardar cambios
        </button>

    </form>

    <hr>

<h2>Resumen del objetivo</h2>

<p>
    <strong>Tareas totales:</strong>
    <?php echo htmlspecialchars($totalTareasObjetivo); ?>
</p>

<p>
    <strong>Tareas completadas:</strong>
    <?php echo htmlspecialchars($tareasCompletadasObjetivo); ?>
</p>

<p>
    <strong>Sesiones Pomodoro registradas:</strong>
    <?php echo htmlspecialchars($totalSesionesObjetivo); ?>
</p>

<p>
    <strong>Tiempo invertido en este objetivo:</strong>
    <?php echo htmlspecialchars($horasObjetivo); ?> h
    <?php echo htmlspecialchars($minutosRestantesObjetivo); ?> min
</p>

    <hr>

    <h2>Tareas del objetivo</h2>

<?php if (empty($tareasObjetivo)): ?>

    <p>Este objetivo todavía no tiene tareas creadas.</p>

<?php else: ?>

    <div style="display: flex; flex-wrap: wrap; gap: 15px;">

        <?php foreach ($tareasObjetivo as $tarea): ?>

            <div style="border: 1px solid #ccc; padding: 12px; width: 250px;">

                <h3><?php echo htmlspecialchars($tarea["titulo"]); ?></h3>

                <p>
                    <strong>Estado:</strong>
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
                    <a href="tarea_detalle.php?id_tarea=<?php echo $tarea["id_tarea"]; ?>">
                        Entrar a la tarea
                    </a>
                </p>

            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

<p>
    <a href="tareas.php?id_objetivo=<?php echo $objetivo["id_objetivo"]; ?>">
        Crear o gestionar tareas
    </a>
</p>

    <hr>

    <h2>Eliminar objetivo</h2>
    <p>Esta acción eliminará el objetivo y todas sus tareas asociadas.</p>

    <form method="POST" action="" onsubmit="return confirm('¿Seguro que quieres eliminar este objetivo? También se eliminarán sus tareas asociadas.');">
        <button type="submit" name="eliminar_objetivo">
        Eliminar objetivo
        </button>
    </form>

</body>

</html>