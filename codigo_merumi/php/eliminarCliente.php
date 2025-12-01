<?php
// devolver respuesta en JSON
header('Content-Type: application/json');

include 'conexion.php';

// respuesta por defecto
$response = ['success' => false, 'message' => ''];

// verificar id_usuario recibido
if (!isset($_POST['id_usuario']) || $_POST['id_usuario'] === '') {
    http_response_code(400);
    $response['message'] = 'ID de usuario no proporcionado.';
    echo json_encode($response);
    exit;
}

$id_usuario = $_POST['id_usuario'];

// iniciar transaccion
$conexion->begin_transaction();

try {
    // eliminar usuario cliente (id_Tipo_Persona = 1)
    $stmt = $conexion->prepare(
        "DELETE FROM usuario 
         WHERE id_usuario = ? 
         AND id_Tipo_Persona = 1"
    );

    if (!$stmt) {
        throw new Exception($conexion->error);
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    // verificar eliminacion
    if ($stmt->affected_rows > 0) {
        $conexion->commit();
        $response['success'] = true;
        $response['message'] = 'Usuario eliminado correctamente.';
    } else {
        $conexion->rollback();
        $response['message'] = 'No se encontró el usuario o ya fue eliminado.';
    }

    $stmt->close();

} catch (Exception $e) {
    // error en la operacion
    $conexion->rollback();
    http_response_code(500);
    $response['message'] = 'Error del servidor.';
}

// enviar respuesta
echo json_encode($response);
