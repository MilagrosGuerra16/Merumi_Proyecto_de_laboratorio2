<?php
// Incluye la conexión a la base de datos
include 'conexion.php';

// Respuesta en formato JSON
header('Content-Type: application/json');

// Respuesta por defecto
$response = ['success' => false, 'message' => ''];

// Verifica que llegue el id del usuario por POST
if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
    http_response_code(400);
    $response['message'] = 'ID de usuario no proporcionado.';
    echo json_encode($response);
    exit();
}

$id_usuario = $_POST['id_usuario'];

// Inicia una transacción
$conexion->begin_transaction();

try {
    // Prepara el DELETE solo para usuarios tipo cliente (id_Tipo_Persona = 1)
    $stmt = $conexion->prepare(
        "DELETE FROM usuario WHERE id_usuario = ? AND id_Tipo_Persona = 1"
    );

    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta");
    }

    // Vincula el id del usuario
    $stmt->bind_param("i", $id_usuario);

    // Ejecuta la eliminación
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $conexion->commit();
            $response['success'] = true;
            $response['message'] = 'Usuario eliminado correctamente.';
        } else {
            $conexion->rollback();
            $response['message'] = 'Usuario no encontrado.';
        }
    } else {
        throw new Exception("Error al ejecutar el DELETE");
    }

    $stmt->close();

} catch (Exception $e) {
    // Cancela cambios si ocurre un error
    $conexion->rollback();
    http_response_code(500);
    $response['message'] = 'Error del servidor.';
}

// Devuelve respuesta final
echo json_encode($response);
?>
