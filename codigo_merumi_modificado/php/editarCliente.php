<?php
// Incluir el archivo de conexión a la base de datos (define $conexion)
include 'conexion.php'; 

header('Content-Type: application/json');
$response = array('success' => false, 'message' => '');

// Verificar que se hayan recibido los datos requeridos
if (!isset($_POST['id_usuario'], $_POST['nombre'], $_POST['email'], $_POST['celular'])) {
    http_response_code(400); // Bad Request
    $response['message'] = 'Faltan datos requeridos para la actualización.';
    echo json_encode($response);
    exit();
}

$id_usuario = $_POST['id_usuario'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$celular = $_POST['celular'];

try {
    // Preparar la consulta UPDATE
    $stmt = $conexion->prepare("UPDATE usuario SET nombre = ?, email = ?, celular = ? WHERE id_usuario = ? AND id_Tipo_Persona = 1");
    
    // Verificar si la preparación falló
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    // Vincular parámetros (s = string, s = string, s = string, i = integer)
    $stmt->bind_param("sssi", $nombre, $email, $celular, $id_usuario);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Verificar si se afectaron filas (si realmente se realizó un cambio)
        if ($stmt->affected_rows >= 0) { // Usamos >= 0 porque affected_rows es 0 si no hubo cambios pero la consulta fue exitosa
            $response['success'] = true;
            $response['message'] = 'Usuario actualizado correctamente.';
        } else {
            // Esto solo ocurre si la consulta falla completamente
            throw new Exception("No se pudo actualizar el usuario.");
        }
    } else {
        throw new Exception("Error al ejecutar la actualización: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>