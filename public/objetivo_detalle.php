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

// Actualizar objetivo
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["actualizar_objetivo"])) {

        $titulo = trim($_POST["titulo"]);
        $descripción = trim($_POST["descripcion"]);
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
            $stmt->execute([$titulo, $descripción, $estado, $idObjetivo, $idUsuario]);

            header("Location: objetivo_detalle.php?id_objetivo" . $idObjetivo);
            exit;

        }
    }


// Cambiar estado del objetivo
/*    if (isset($_POST["cambiar_estado"])) {

        $sql = "UPDATE objetivos
                SET estado = ?
                WHERE id_objetivo = ? AND id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuevoEstado, $idObjetivo, $idUsuario]);

        header("Location: objetivo_detalle.php?id_objetivo=" . $idObjetivo);
        exit;
    }
*/

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
        <a href="objetivos.php">Volver a mis objetivos</a>
        <a href="dasboard.php">Panel principal</a>
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
        <p stlye="color: red;"><?php echo htmlspecialchars($mensaje); ?></p>
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

    <h2>Tareas del objetivo</h2>

    <p>
        Desde esta sección puedes acceder a la gestión de tareas del objetivo.
        Allí podrás crear tareas, completarlas, eliminarlas o entrar en una tarea concreta.
    </p>

    <p>
        
        <a href="tareas.php?id_objetivo=<?php echo $objetivo["id_objetivo"]; ?>">
        Ver y crear tareas de este objetivo
        </a>
    
    </p>

    <hr>

    <h2>Zona peligrosa</h2>

    <form method="POST" action="" onsubmit="return confirm('¿Seguro que quieres eliminar este objetivo? También se eliminarán sus tareas asociadas.');">
        <button type="submit" name="eliminar_objetivo">
        Eliminar objetivo
        </button>
    </form>

</body>

</html>