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
    <title>Mis objetivos - Organica</title>
</head>

<body>

    <h1>Mis objetivos</h1>

    <p>Usuario: <?php echo htmlspecialchars($_SESSION["nombre"]); ?></p>

    <nav>

    <nav>
        <a href="dashboard.php">Panel principal</a> |
        <a href="calendario.php">Calendario</a> |
        <a href="estadisticas.php">Estadísticas</a> |
        <a href="logout.php">Cerrar sesión</a>
    </nav>
    
    </nav>

    <hr>

    <h2>Crear nuevo objetivo</h2>

    <?php if (!empty($mensaje)):  ?>
    
        <p style="color: red;"><?php echo htmlspecialchars($mensaje); ?></p>

    <?php endif; ?>

    <form method="POST" action="">

        <label for="titulo">Título del objetivo:</label><br>
        <input type="text" id="titulo" name="titulo"><br><br>

        <label for="descripcion">Descripción:</label><br>
        <textarea id="descripcion" name="descripcion" rows="4" cols="40"></textarea><br><br>

        <button type="submit" name="crear_objetivo">Crear objetivo</button>

    </form>

    <hr>

    <h2>Tu panel de objetivos</h2>

    <?php if (empty($objetivos)): ?>
        
        <p>Todavía no tienes objetibvos creados.</p>

    <?php else: ?>

    
    <div style="display: flex; flex-wrap: wrap; gap: 15px;">
        <?php foreach ($objetivos as $objetivo): ?>
            <div style="border: 1px solid #ccc; padding: 15px; width: 250px; min-height: 160px;">

                <h3><?php echo htmlspecialchars($objetivo["titulo"]); ?></h3>

                <p>
                    <?php echo nl2br(htmlspecialchars($objetivo["descripcion"])); ?>
                </p>

                <p>
                    <strong>Estado:</strong>
                    <?php echo htmlspecialchars($objetivo["estado"]); ?>
                </p>

                <p>
                    <a href="objetivo_detalle.php?id_objetivo=<?php echo $objetivo["id_objetivo"]; ?>">
                        Entrar al objetivo
                    </a>
                </p>

            </div>

            <?php endforeach; ?>
    </div>

    <?php endif; ?>

</body>

</html>