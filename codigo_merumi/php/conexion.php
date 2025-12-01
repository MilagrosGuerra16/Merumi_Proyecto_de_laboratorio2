<?php
// datos de conexion a la base de datos
$servidor     = "localhost";
$usuario_db   = "merumi_webmaster";
$contrasena_db = "Criis217217";
$nombre_db    = "merumi_ramen";

// crear conexion
$conexion = new mysqli(
    $servidor,
    $usuario_db,
    $contrasena_db,
    $nombre_db
);

// verificar conexion
if ($conexion->connect_error) {
    header('Content-Type: application/json');

    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos',
        'error_usuario' => 'Error interno del servidor'
    ]);
    exit;
}

// setear charset utf8
$conexion->set_charset("utf8");
