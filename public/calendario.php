<?php
session_start();
require_once "../app/config/database.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION["id_usuario"];
$anio = date("Y");

if (isset($_GET["anio"])) {
    $anio = $_GET["anio"];
}

if (!is_numeric($anio) || $anio < 2000 || $anio > 2100) {
    $anio = date("Y");
}

// Api de festivos
$urlApi = "https://date.nager.at/api/v3/PublicHolidays/" . $anio . "/ES";
$respuestaApi = @file_get_contents($urlApi);

$festivos = [];
$errorApi = "";

if ($respuestaApi === false) {
    $errorApi = "No se pudieron cargar los festivos desde la API.";
} else {
    $festivos = json_decode($respuestaApi, true);

    if (!is_array($festivos)) {
        $festivos = [];
        $errorApi = "La respuesta de la API no tiene el formato esperado.";
    }
}

// Fechas límite
$sql = "SELECT tareas.titulo, tareas.fecha_limite, tareas.estado, objetivos.titulo AS titulo_objetivo
        FROM tareas
        INNER JOIN objetivos ON tareas.id_objetivo = objetivos.id_objetivo
        WHERE objetivos.id_usuario = ?
        AND tareas.fecha_limite IS NOT NULL
        ORDER BY tareas.fecha_limite ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$idUsuario]);
$tareasConFecha = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Añadir eventos para FullCalendar
$eventosCalendario = [];

foreach ($festivos as $festivo) {
    if (isset($festivo["date"], $festivo["localName"])) {
        $eventosCalendario[] = [
            "title" => "Festivo: " . $festivo["localName"],
            "start" => $festivo["date"],
            "allDay" => true,
            "color" => "#dc3545"
        ];
    }
}

// Convertir tareas con fecha límite en eventos del calendario
foreach ($tareasConFecha as $tarea) {
    $eventosCalendario[] = [
        "title" => "Tarea: " . $tarea["titulo"],
        "start" => $tarea["fecha_limite"],
        "allDay" => true,
        "color" => ($tarea["estado"] === "completada") ? "#198754" : "#0d6efd"
    ];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Calendario - Organica</title>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
</head>

<body>

    <nav>
        <a href="dashboard.php">Panel principal</a> |
        <a href="objetivos.php">Mis objetivos</a> |
        <a href="estadisticas.php">Estadísticas</a> |
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <hr>

    <h1>Calendario de planificación</h1>

    <p>
        En esta sección se muestran los festivos oficiales de España obtenidos mediante API
        y las tareas del usuario que tienen fecha límite.
    </p>

    <form method="GET" action="">
        <label for="anio">Año:</label>
        <input type="number" id="anio" name="anio" value="<?php echo htmlspecialchars($anio); ?>">

        <button type="submit">Consultar</button>
    </form>



    <hr>



    <h2>Calendario visual - <?php echo htmlspecialchars($anio); ?></h2>

<?php if (!empty($errorApi)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($errorApi); ?></p>
<?php endif; ?>

<div style="margin-bottom: 15px;">
    <p>
        <strong>Leyenda:</strong>
        <span style="color: #dc3545;">■ Festivos</span>
        |
        <span style="color: #0d6efd;">■ Tareas pendientes</span>
        |
        <span style="color: #198754;">■ Tareas completadas</span>
    </p>
</div>

<div id="calendar"></div>

<hr>

<h2>Listado rápido de tareas con fecha límite</h2>

<?php if (empty($tareasConFecha)): ?>

    <p>No tienes tareas con fecha límite.</p>

<?php else: ?>

    <ul>
        <?php foreach ($tareasConFecha as $tarea): ?>
            <li>
                <?php echo htmlspecialchars($tarea["fecha_limite"]); ?>
                -
                <?php echo htmlspecialchars($tarea["titulo"]); ?>
                /
                Objetivo:
                <?php echo htmlspecialchars($tarea["titulo_objetivo"]); ?>
                /
                Estado:
                <?php echo htmlspecialchars($tarea["estado"]); ?>
            </li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const calendarEl = document.getElementById("calendar");

        const eventosOrganica = <?php echo json_encode($eventosCalendario, JSON_UNESCAPED_UNICODE); ?>;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            initialDate: "<?php echo htmlspecialchars($anio); ?>-01-01",
            locale: "es",
            height: "auto",
            firstDay: 1,
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,listMonth"
            },
            events: eventosOrganica
        });

        calendar.render();
    });
    
</script>
</body>

</html>