<?php
// devolver respuesta en JSON
header('Content-Type: application/json');

include 'conexion.php';

// validar datos recibidos
if (
    !isset($_POST['id_usuario']) ||
    !isset($_POST['nombre']) ||
    !isset($_POST['email'])  ||
    !isset($_POST['celular'])
) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos'
    ]);
    exit;
}

$id_usuario = intval($_POST['id_usuario']);
$nombre     = $_POST['nombre'];
$email      = $_POST['email'];
$celular    = $_POST['celular'];

// actualizar administrador (id_Tipo_Persona = 2)
$stmt = $conexion->prepare(
    "UPDATE usuario 
     SET nombre = ?, email = ?, celular = ?
     WHERE id_usuario = ? AND id_Tipo_Persona = 2"
);

$stmt->bind_param("sssi", $nombre, $email, $celular, $id_usuario);

// ejecutar actualizacion
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar'
    ]);
}

// cerrar consulta
$stmt->close();
