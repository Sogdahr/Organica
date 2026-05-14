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
    <p>En el siguiente bloque se podrán añadir subtareas a esta tarea.</p>

    <h2>Notas</h2>
    <p>En un bloque posterior se podrán añadir notas internas a esta tarea.</p>

    <h2>Pomodoro</h2>
    <p>En un bloque posterior se añadirá el temporizador Pomodoro asociado a esta tarea.</p>

    <hr>

    <h2>Zona peligrosa</h2>

    <form method="POST" action="" onsubmit="return confirm('¿Seguro que quieres eliminar esta tarea?');">
        <button type="submit" name="eliminar_tarea">
            Eliminar tarea
        </button>
    </form>

</body>

</html>