<?php
session_start();
require_once "../app/config/database.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION["id_usuario"];
$mensaje = "";

// Crear nuevo objetivo
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_objetivo"])) {

    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);

    if (empty($titulo)) {

        $mensaje = "El título del objetivo es obligatorio.";

    } else {

        $sql = "INSERT INTO objetivos (id_usuario, titulo, descripcion) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idUsuario, $titulo, $descripcion]);

        header("Location: objetivos.php");
        exit;
    }
}

// Obtener objetivos del usuario conectado
$sql = "SELECT * FROM objetivos WHERE id_usuario = ? ORDER BY fecha_creacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$objetivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis objetivos - Organica</title>

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
            <span class="etiqueta-seccion">Tablero de objetivos</span>

            <h1>Mis objetivos</h1>

            <p class="texto-bienvenida">
                Bienvenido/a, <strong><?php echo htmlspecialchars($_SESSION["nombre"]); ?></strong>.
                En esta zona puedes crear objetivos y verlos como tarjetas dentro de tu tablero personal.
            </p>
        </div>
    </section>

    <section class="bloque-papel mb-5">
        <h2 class="titulo-seccion">Crear nuevo objetivo</h2>

        <?php if (!empty($mensaje)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label for="titulo" class="form-label">Título del objetivo:</label>
                <input type="text" id="titulo" name="titulo" class="form-control">
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="form-control"></textarea>
            </div>

            <button type="submit" name="crear_objetivo" class="btn btn-organica">
                Crear objetivo
            </button>

        </form>
    </section>

    <section>
        <h2 class="titulo-seccion">Tu panel de objetivos</h2>

        <?php if (empty($objetivos)): ?>

            <div class="bloque-papel">
                <p>Todavía no tienes objetivos creados.</p>
            </div>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($objetivos as $objetivo): ?>

                    <div class="col-md-4">
                        <article class="tarjeta-objetivo h-100">
                            <span class="pin"></span>

                            <?php if ($objetivo["estado"] === "completado"): ?>
                                <span class="estado estado-completado">Completado</span>
                            <?php else: ?>
                                <span class="estado estado-pendiente">Pendiente</span>
                            <?php endif; ?>

                            <h3><?php echo htmlspecialchars($objetivo["titulo"]); ?></h3>

                            <p>
                                <?php echo nl2br(htmlspecialchars($objetivo["descripcion"])); ?>
                            </p>

                            <div class="barra-progreso">
                                <div style="width: <?php echo ($objetivo["estado"] === "completado") ? '100%' : '35%'; ?>;"></div>
                            </div>

                            <div class="datos-objetivo">
                                <span>Estado</span>
                                <span><?php echo htmlspecialchars($objetivo["estado"]); ?></span>
                            </div>

                            <a href="objetivo_detalle.php?id_objetivo=<?php echo $objetivo["id_objetivo"]; ?>" class="btn btn-sm btn-organica mt-3">
                                Entrar al objetivo
                            </a>
                        </article>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>