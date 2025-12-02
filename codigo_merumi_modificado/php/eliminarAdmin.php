<?php
// Incluye la conexión a la base de datos
include 'conexion.php';

// Respuesta en formato JSON
header('Content-Type: application/json');

// Verifica que llegue el id del usuario
if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos'
    ]);
    exit;
}

// Convierte el id a entero por seguridad
$id_usuario = intval($_POST['id_usuario']);

// Prepara el DELETE solo para admins (id_Tipo_Persona = 2)
$stmt = $conexion->prepare(
    "DELETE FROM usuario 
     WHERE id_usuario = ? AND id_Tipo_Persona = 2"
);

// Vincula el parámetro
$stmt->bind_param("i", $id_usuario);

// Ejecuta la consulta
if ($stmt->execute()) {
    // Si se eliminó algún registro
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        // No se encontró el admin
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el admin'
        ]);
    }
} else {
    // Error al ejecutar el DELETE
    echo json_encode([
        'success' => false,
        'message' => 'Error al ejecutar'
    ]);
}

// Cierra la consulta
$stmt->close();
?>
