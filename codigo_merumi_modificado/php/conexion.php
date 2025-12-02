<?php
// Datos de conexión a la base de datos
$servidor = "localhost"; 
$usuario_db = "merumi_webmaster";    
$contrasena_db = "Criis217217";     
$nombre_db = "merumi_ramen";   

// Crea la conexión a MySQL
$conexion = new mysqli($servidor, $usuario_db, $contrasena_db, $nombre_db);

// Verifica si hubo error al conectar
if ($conexion->connect_error) {

    // Indica que la respuesta será en formato JSON
    header('Content-Type: application/json');
    
    // Respuesta de error
    $response = [
        'success' => false,
        
        'message' => 'Error de conexión a la base de datos: ' . $conexion->connect_error,
        
        'error_usuario' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
    ];
    
    // Devuelve el error y corta la ejecución
    echo json_encode($response);
    exit();
}

// Define el juego de caracteres
$conexion->set_charset("utf8");

?>
