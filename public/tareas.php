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

$idUsuario = $_SESSION["id_usuario"];
$idObjetivo = $_GET["id_objetivo"];

// Mensajes 
$mensajeTarea = "";
$mensajeSubtarea = "";
$mensajeNota = "";
$mensajePomodoro = "";

// Comprobar que el objetivo pretenece al usuario conectado
    $sql = "SELECT * FROM objetivos WHERE id_objetivo = ? AND id_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idObjetivo, $idUsuario]);
    $objetivo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$objetivo) {
            header("Location: objetivos.php");
            exit;
        }

// Crear una nueva tarea
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_tarea"])) {

    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $fechaLimite = $_POST["fecha_limite"];
    $prioridad = $_POST["prioridad"];

        if (empty($titulo)) {
            $mensajeTarea = "El titulo de la tarea es obligatorio.";
        } elseif ($prioridad !== "baja" && $prioridad !== "media" && $prioridad !== "alta"){
            $mensajeTarea = "La prioridad seleccionada no es válida";
        } else {
            if (empty($fechaLimite)) {
                $fechaLimite = null;
            }

        $sql = "INSERT INTO tareas (id_objetivo, titulo, descripcion, fecha_limite, prioridad)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idObjetivo, $titulo, $descripcion, $fechaLimite, $prioridad]);

        header("Location: tareas.php?id_objetivo=" . $idObjetivo);
        exit;
    
    }
}

// Cambiar estado de una tarea
if (isset($_GET["cambiar_estado"])) {
    $idTarea = $_GET["cambiar_estado"];

    $sql = "SELECT * FROM tareas 
            WHERE id_tarea = ? AND id_objetivo = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idTarea, $idObjetivo]);
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tarea) {
        $nuevoEstado = ($tarea["estado"] === "pendiente") ? "completada" : "pendiente";

        $sql = "UPDATE tareas 
                SET estado = ? 
                WHERE id_tarea = ? AND id_objetivo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevoEstado, $idTarea, $idObjetivo]);
    }

    header("Location: tareas.php?id_objetivo=" . $idObjetivo);
    exit;
}

// Eliminar tarea
if (isset($_GET["eliminar"])) {
    $idTarea = $_GET["eliminar"];

    $sql = "DELETE FROM tareas 
            WHERE id_tarea = ? AND id_objetivo = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idTarea, $idObjetivo]);

    header("Location: tareas.php?id_objetivo=" . $idObjetivo);
    exit;
}

// Obtener tareas del objetivo
    $sql = "SELECT * FROM tareas 
            WHERE id_objetivo = ?
            ORDER BY fecha_creacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idObjetivo]);
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tareas - Organica</title>
</head>

<body>

    <nav>
        <a href="objetivo_detalle.php?id_objetivo=<?php echo $idObjetivo; ?>">Volver al objetivo</a> |
        <a href="objetivos.php">Mis objetivos</a> |
        <a href="dashboard.php">Panel principal</a> |
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <hr>

    <h1>Tareas del objetivo</h1>

    <h2><?php echo htmlspecialchars($objetivo["titulo"]); ?></h2>

    <p>
        <?php echo nl2br(htmlspecialchars($objetivo["descripcion"])); ?>
    </p>

    <hr>

    <h2>Crear nueva tarea</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="titulo">Título de la tarea:</label><br>
        <input type="text" id="titulo" name="titulo"><br><br>

        <label for="descripcion">Descripción:</label><br>
        <textarea id="descripcion" name="descripcion" rows="4" cols="50"></textarea><br><br>

        <label for="fecha_limite">Fecha límite:</label><br>
        <input type="date" id="fecha_limite" name="fecha_limite"><br><br>

        <label for="prioridad">Prioridad:</label><br>
        <select id="prioridad" name="prioridad">
            <option value="baja">Baja</option>
            <option value="media" selected>Media</option>
            <option value="alta">Alta</option>
        </select><br><br>

        <button type="submit" name="crear_tarea">Crear tarea</button>

    </form>

    <hr>

    <h2>Listado de tareas</h2>

    <?php if (empty($tareas)): ?>

        <p>Este objetivo todavía no tiene tareas creadas.</p>

    <?php else: ?>

        <?php foreach ($tareas as $tarea): ?>

            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px;">

                <h3><?php echo htmlspecialchars($tarea["titulo"]); ?></h3>

                <p>
                    <?php echo nl2br(htmlspecialchars($tarea["descripcion"])); ?>
                </p>

                <p>
                    <strong>Fecha límite:</strong>
                    <?php echo htmlspecialchars($tarea["fecha_limite"] ?? "Sin fecha"); ?>
                </p>

                <p>
                    <strong>Prioridad:</strong>
                    <?php echo htmlspecialchars($tarea["prioridad"]); ?>
                </p>

                <p>
                    <strong>Estado:</strong>
                    <?php echo htmlspecialchars($tarea["estado"]); ?>
                </p>

                <p>
                    <a href="tarea_detalle.php?id_tarea=<?php echo $tarea["id_tarea"]; ?>">
                        Entrar a la tarea
                    </a>
                    |
                    <a href="tareas.php?id_objetivo=<?php echo $idObjetivo; ?>&cambiar_estado=<?php echo $tarea["id_tarea"]; ?>">
                        Cambiar estado
                    </a>
                    |
                    <a href="tareas.php?id_objetivo=<?php echo $idObjetivo; ?>&eliminar=<?php echo $tarea["id_tarea"]; ?>"
                       onclick="return confirm('¿Seguro que quieres eliminar esta tarea?');">
                        Eliminar
                    </a>
                </p>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</body>

</html>