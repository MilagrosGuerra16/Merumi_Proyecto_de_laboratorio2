<?php
include 'conexion.php';

header('Content-Type: application/json');

if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos'
    ]);
    exit;
}

$id_usuario = intval($_POST['id_usuario']);

$stmt = $conexion->prepare(
    "DELETE FROM usuario 
     WHERE id_usuario = ? AND id_Tipo_Persona = 2"
);

$stmt->bind_param("i", $id_usuario);

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

$stmt->close();
