<?php
// Respuesta en formato JSON
header('Content-Type: application/json');

// Conexión a la base de datos
require_once 'conexion.php';

// Función para responder en JSON y cortar ejecución
function responseJson($success, $message, $codigo = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'codigo'  => $codigo
    ]);
    exit;
}

// Solo permite peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJson(false, 'Método no permitido.');
}

// Obtener email
$email = $_POST['email'] ?? '';

// Validar email
if (empty($email)) {
    responseJson(false, 'Debes ingresar un correo electrónico.');
}

// Buscar usuario por email
$sql = "SELECT id_usuario FROM usuario WHERE email = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Si no existe el usuario
if ($result->num_rows === 0) {
    responseJson(false, 'Correo no registrado.');
}

$user = $result->fetch_assoc();
$stmt->close();

// Generar código y tiempo de expiración
$codigo = rand(100000, 999999);
$expiracion = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// Guardar código y expiración
$sql_update = "UPDATE usuario SET codigo_recuperacion = ?, expiracion_codigo = ? WHERE id_usuario = ?";
$stmt_update = $conexion->prepare($sql_update);
$stmt_update->bind_param("ssi", $codigo, $expiracion, $user['id_usuario']);
$stmt_update->execute();

// Cerrar conexión
$stmt_update->close();
$conexion->close();

// Respuesta final
responseJson(true, 'Código enviado correctamente.', $codigo);
