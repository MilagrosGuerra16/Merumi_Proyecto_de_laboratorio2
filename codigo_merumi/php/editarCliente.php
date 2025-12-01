<?php
// devolver respuesta en JSON
header('Content-Type: application/json');

include 'conexion.php';

// respuesta por defecto
$response = ['success' => false, 'message' => ''];

// validar datos recibidos
if (
    !isset($_POST['id_usuario'], $_POST['nombre'], $_POST['email'], $_POST['celular'])
) {
    http_response_code(400);
    $response['message'] = 'Faltan datos requeridos.';
    echo json_encode($response);
    exit;
}

$id_usuario = $_POST['id_usuario'];
$nombre     = $_POST['nombre'];
$email      = $_POST['email'];
$celular    = $_POST['celular'];

try {
    // actualizar datos del usuario cliente (id_Tipo_Persona = 1)
    $stmt = $conexion->prepare(
        "UPDATE usuario 
         SET nombre = ?, email = ?, celular = ?
         WHERE id_usuario = ? AND id_Tipo_Persona = 1"
    );

    if (!$stmt) {
        throw new Exception($conexion->error);
    }

    $stmt->bind_param("sssi", $nombre, $email, $celular, $id_usuario);
    $stmt->execute();

    // actualizacion exitosa
    $response['success'] = true;
    $response['message'] = 'Usuario actualizado correctamente.';

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Error del servidor.';
}

// enviar respuesta
echo json_encode($response);
