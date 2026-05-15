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
            $mensajeTarea = "El título de la tarea es obligatorio.";
        } elseif ($prioridad !== "baja" && $prioridad !== "media" && $prioridad !== "alta"){
            $mensajeTarea = "La prioridad seleccionada no es válida.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas - Organica</title>

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
                <li class="nav-item"><a class="nav-link" href="objetivo_detalle.php?id_objetivo=<?php echo $idObjetivo; ?>">Volver al objetivo</a></li>
                <li class="nav-item"><a class="nav-link cerrar-sesion" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">

    <section class="hero-corcho mb-5">
        <div class="hero-papel">
            <span class="etiqueta-seccion">Checklist del objetivo</span>

            <h1>Tareas del objetivo</h1>

            <p class="texto-bienvenida">
                <strong><?php echo htmlspecialchars($objetivo["titulo"]); ?></strong><br>
                <?php echo nl2br(htmlspecialchars($objetivo["descripcion"])); ?>
            </p>
        </div>
    </section>

    <section class="bloque-papel mb-5">
        <h2 class="titulo-seccion">Crear nueva tarea</h2>

        <?php if (!empty($mensajeTarea)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($mensajeTarea); ?></p>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label for="titulo" class="form-label">Título de la tarea:</label>
                <input type="text" id="titulo" name="titulo" class="form-control">
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="form-control"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_limite" class="form-label">Fecha límite:</label>
                    <input type="date" id="fecha_limite" name="fecha_limite" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="prioridad" class="form-label">Prioridad:</label>
                    <select id="prioridad" name="prioridad" class="form-select">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="crear_tarea" class="btn btn-organica">
                Crear tarea
            </button>

        </form>
    </section>

    <section>
        <h2 class="titulo-seccion">Listado de tareas</h2>

        <?php if (empty($tareas)): ?>

            <div class="bloque-papel">
                <p>Este objetivo todavía no tiene tareas creadas.</p>
            </div>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($tareas as $tarea): ?>

                    <div class="col-md-4">
                        <article class="tarjeta-tarea h-100">

                            <h3><?php echo htmlspecialchars($tarea["titulo"]); ?></h3>

                            <p class="mb-3">
                                <?php echo nl2br(htmlspecialchars($tarea["descripcion"])); ?>
                            </p>

                            <p>
                                <span class="dato-mini"><?php echo htmlspecialchars($tarea["estado"]); ?></span>
                                <span class="dato-mini"><?php echo htmlspecialchars($tarea["prioridad"]); ?></span>
                                <span class="dato-mini">
                                    <?php echo htmlspecialchars($tarea["fecha_limite"] ?? "Sin fecha"); ?>
                                </span>
                            </p>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="tarea_detalle.php?id_tarea=<?php echo $tarea["id_tarea"]; ?>" class="btn btn-sm btn-organica">
                                    Entrar
                                </a>

                                <a href="tareas.php?id_objetivo=<?php echo $idObjetivo; ?>&cambiar_estado=<?php echo $tarea["id_tarea"]; ?>" class="btn btn-sm btn-organica-outline">
                                    Cambiar estado
                                </a>

                                <a href="tareas.php?id_objetivo=<?php echo $idObjetivo; ?>&eliminar=<?php echo $tarea["id_tarea"]; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('¿Seguro que quieres eliminar esta tarea?');">
                                    Eliminar
                                </a>
                            </div>

                        </article>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

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