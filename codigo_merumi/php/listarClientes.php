<?php

include 'conexion.php';

//  JSON
header('Content-Type: application/json');

//  almacenar los usuarios
$usuarios = array();

try {
    //  id_Tipo_Persona = 1
    $sql = "SELECT id_usuario, nombre, email, celular FROM usuario WHERE id_Tipo_Persona = 1";
    
 
    $resultado = $conexion->query($sql);
    
    // Verificar si la consulta fue exitosa
    if ($resultado === false) {
        throw new Exception("Error en la consulta SQL: " . $conexion->error);
    }
    
    // Obtener todos los resultados y guardarlos en el array $usuarios
    if ($resultado->num_rows > 0) {
        while($fila = $resultado->fetch_assoc()) {
            $usuarios[] = $fila;
        }
    }
    
    // Devolver el array de usuarios en formato JSON
    echo json_encode($usuarios);

} catch (Exception $e) {
    // Capturar cualquier error 
    http_response_code(500);
    // Devolver un objeto JSON con el error
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}

// Cerrar la conexión 
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}
?>