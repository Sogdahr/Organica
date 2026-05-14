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

$sql = "SELECT * FROM objetivos WHERE id_objetivo = ? AND id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$idObjetivo, $idUsuario]);
$objetivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$objetivo) {
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

    <h1><?php echo htmlspecialchars($objetivo["titulo"]); ?></h1>

    <p><?php echo nl2br(htmlspecialchars($objetivo["descripcion"])); ?></p>
    
    <p>
        <strong>Estado:</strong>
        <?php echo htmlspecialchars($objetivo["estado"]); ?>
    </p>

    <hr>

    <h2>Panel del objetivo</h2>

    <p>Desde aquí se gestionarán las tareas, notas, tiempos y estadísticas de este objetivo.</p>

    <nav>
        <a href="objetivos.php">Volver a mis objetivos</a>
        <a href="dasboard.php">Panel principal</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
    
</body>

</html>