<?php
// Conecta con la base de datos
include 'conexion.php';

// Respuesta en formato JSON
header('Content-Type: application/json');

// Array donde se guardan los clientes
$usuarios = [];

try {
    // Trae solo usuarios tipo cliente (id_Tipo_Persona = 1)
    $sql = "SELECT id_usuario, nombre, email, celular FROM usuario WHERE id_Tipo_Persona = 1";
    $resultado = $conexion->query($sql);

    // Si falla la consulta, se corta
    if ($resultado === false) {
        throw new Exception($conexion->error);
    }

    // Guarda cada usuario en el array
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }

    // Devuelve los usuarios en JSON
    echo json_encode($usuarios);

} catch (Exception $e) {
    // Error de base de datos
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos']);
}

// Cierra la conexión
$conexion->close();
