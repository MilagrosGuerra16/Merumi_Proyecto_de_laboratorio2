<?php
// Configuración de la base de datos
$servidor = "localhost"; // Generalmente es 'localhost' en XAMPP
$usuario_db = "root";    // Generalmente es 'root' en XAMPP
$contrasena_db = "";     // Generalmente es vacío ("") en XAMPP
$nombre_db = "merumi_ramen";   // ¡Asegúrate de que este sea el nombre CORRECTO de tu base de datos!

// Crear la conexión
$conexion = new mysqli($servidor, $usuario_db, $contrasena_db, $nombre_db);

// Verificar la conexión
if ($conexion->connect_error) {
    // Si la conexión falla, imprimimos un mensaje de error claro y detenemos el script
    // NOTA: Esto es para debug, en producción es mejor no mostrar el error completo.
    
    // Configura el encabezado como JSON para que el JS pueda leer la respuesta
    header('Content-Type: application/json');
    
    $response = [
        'success' => false,
        // Mensaje interno para que el desarrollador vea el error real
        'message' => 'Error de conexión a la base de datos: ' . $conexion->connect_error,
        // Mensaje para el usuario
        'error_usuario' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
    ];
    
    echo json_encode($response);
    exit();
}

// Opcional: Establecer el juego de caracteres a utf8
$conexion->set_charset("utf8");

// NOTA: Si este archivo se ejecuta correctamente, $conexion estará disponible en enviar_codigo.php

?>