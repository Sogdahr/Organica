<?php
require_once "../app/config/database.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organica</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<main class="container py-5">

    <section class="hero-corcho">
        <div class="hero-papel">

            <div class="row align-items-center">

                <div class="col-lg-7">
                    <span class="etiqueta-seccion">Productividad personal</span>

                    <h1>Organica</h1>

                    <p class="texto-bienvenida">
                        Organiza objetivos grandes en tareas, subtareas y notas.
                        Registra tu tiempo con Pomodoro y consulta tu progreso desde un calendario visual.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="registro.php" class="btn btn-organica">
                            Crear cuenta
                        </a>

                        <a href="login.php" class="btn btn-organica-outline">
                            Iniciar sesión
                        </a>
                    </div>
                </div>

                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="zona-postit">

                        <div class="dibujo-maceta">
                            <div class="tallo"></div>
                            <div class="hoja hoja-1"></div>
                            <div class="hoja hoja-2"></div>
                            <div class="maceta"></div>
                        </div>

                        <div class="postit">
                            <span class="chincheta"></span>
                            <p>Objetivos claros,<br>pasos pequeños.</p>
                            <span class="corazon">♡</span>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </section>

    <section class="mt-5">
        <h2 class="titulo-seccion">Qué puedes hacer con Organica</h2>

        <div class="row g-4">

            <div class="col-md-4">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">◎</div>
                    <h3>Objetivos</h3>
                    <p class="descripcion">
                        Crea metas personales y divídelas en tareas manejables.
                    </p>
                </article>
            </div>

            <div class="col-md-4">
                <article class="tarjeta-resumen h-100 tarjeta-hoja">
                    <span class="pin"></span>
                    <div class="icono-resumen">☑</div>
                    <h3>Tareas</h3>
                    <p class="descripcion">
                        Organiza tareas, subtareas y notas para no perder el seguimiento.
                    </p>
                </article>
            </div>

            <div class="col-md-4">
                <article class="tarjeta-resumen h-100">
                    <span class="pin"></span>
                    <div class="icono-resumen">◷</div>
                    <h3>Pomodoro</h3>
                    <p class="descripcion">
                        Registra sesiones de trabajo y consulta el tiempo invertido.
                    </p>
                </article>
            </div>

        </div>
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