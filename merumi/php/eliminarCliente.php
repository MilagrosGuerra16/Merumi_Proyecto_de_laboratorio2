<?php
// Incluir el archivo de conexión a la base de datos (define $conexion)
include 'conexion.php'; 

header('Content-Type: application/json');
$response = array('success' => false, 'message' => '');

// Verificar si se recibió el id_usuario por POST
if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
    http_response_code(400); // Bad Request
    $response['message'] = 'ID de usuario no proporcionado.';
    echo json_encode($response);
    exit();
}

$id_usuario = $_POST['id_usuario'];

// Iniciar transacción (opcional, pero buena práctica)
$conexion->begin_transaction();

try {
    // Escapar la variable para prevenir inyección SQL
    // Aunque mysqli::real_escape_string es preferible, usaremos prepare para mayor seguridad
    
    // Preparar la consulta
    $stmt = $conexion->prepare("DELETE FROM usuario WHERE id_usuario = ? AND id_Tipo_Persona = 1");
    
    // Verificar si la preparación falló
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    // Vincular el parámetro (s = string, i = integer, etc. Usaremos 'i' si id_usuario es INT)
    // Asumiendo que id_usuario es un entero (integer)
    $stmt->bind_param("i", $id_usuario);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $conexion->commit();
            $response['success'] = true;
            $response['message'] = 'Usuario eliminado correctamente.';
        } else {
            $conexion->rollback();
            $response['message'] = 'No se encontró el usuario o ya ha sido eliminado.';
        }
    } else {
        throw new Exception("Error al ejecutar la eliminación: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    // Si hay un error, deshacer la transacción y devolver el error
    $conexion->rollback();
    http_response_code(500);
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>