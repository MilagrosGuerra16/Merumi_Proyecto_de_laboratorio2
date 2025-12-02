<?php
include 'conexion.php';

header('Content-Type: application/json');

if (
    !isset($_POST['id_usuario']) ||
    !isset($_POST['nombre']) ||
    !isset($_POST['email']) ||
    !isset($_POST['celular'])
) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos'
    ]);
    exit;
}

$id_usuario = intval($_POST['id_usuario']);
$nombre  = $_POST['nombre'];
$email   = $_POST['email'];
$celular = $_POST['celular'];

$stmt = $conexion->prepare(
    "UPDATE usuario 
     SET nombre = ?, email = ?, celular = ?
     WHERE id_usuario = ? AND id_Tipo_Persona = 2"
);

$stmt->bind_param("sssi", $nombre, $email, $celular, $id_usuario);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar'
    ]);
}

$stmt->close();
