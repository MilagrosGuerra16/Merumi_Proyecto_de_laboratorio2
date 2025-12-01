<?php
// devolver respuesta en JSON
header('Content-Type: application/json');

include 'conexion.php';

// validar id_usuario
if (!isset($_POST['id_usuario']) || $_POST['id_usuario'] === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos'
    ]);
    exit;
}

$id_usuario = intval($_POST['id_usuario']);

// eliminar administrador (id_Tipo_Persona = 2)
$stmt = $conexion->prepare(
    "DELETE FROM usuario 
     WHERE id_usuario = ? 
     AND id_Tipo_Persona = 2"
);

$stmt->bind_param("i", $id_usuario);

// ejecutar eliminacion
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el admin'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al ejecutar'
    ]);
}

// cerrar consulta
$stmt->close();
