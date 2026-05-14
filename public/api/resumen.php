<?php
header("Content-Type: application/json; charset=UTF-8");

$respuesta = [
    "estado" => "ok",
    "mensaje" => "API de Organica funcionando correctamente",
    "modulo" => "Resumen del sistema",
    "version" => "1.0",
    "tecnologias" => [
        "PHP",
        "MySQL",
        "JavaScript",
        "Bootstrap"
    ]
];

echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>