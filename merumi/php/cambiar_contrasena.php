<?php
// Establece el encabezado para responder en JSON
header('Content-Type: application/json');

// Incluye la conexión a la base de datos y la función de utilidad (si la tienes, si no, defínela aquí)
require_once 'conexion.php'; 

// Función de respuesta JSON
function responseJson(bool $success, string $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// 1. Verificar si se reciben datos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJson(false, 'Método no permitido.');
}

// Obtener y sanitizar datos
$email = $_POST['email'] ?? '';
$code = $_POST['code'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

// Validaciones básicas
if (empty($email) || empty($code) || empty($newPassword)) {
    responseJson(false, 'Faltan campos obligatorios.');
}

// 2. Preparar la consulta SQL para buscar el usuario y el código
// Busca el usuario y verifica que el código y el tiempo de expiración sean válidos
$sql = "SELECT id_usuario, codigo_recuperacion, expiracion_codigo FROM usuario WHERE email = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    responseJson(false, 'Error al preparar la consulta SQL.');
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    responseJson(false, 'Correo electrónico no registrado.');
}

$user = $result->fetch_assoc();
$stmt->close();

$current_time = new DateTime();
$expiration_time = new DateTime($user['expiracion_codigo']);

// 3. Verificar Código y Expiración
if ($user['codigo_recuperacion'] !== $code) {
    responseJson(false, 'Código de verificación incorrecto.');
}

if ($current_time > $expiration_time) {
    responseJson(false, 'El código ha expirado. Vuelve a solicitar la recuperación.');
}

// 4. Actualizar la contraseña
// Encriptar la nueva contraseña antes de guardarla
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Preparar la actualización: nueva contraseña y limpiar el código de recuperación/expiración
$sql_update = "UPDATE usuario SET contraseña = ?, codigo_recuperacion = NULL, expiracion_codigo = NULL WHERE id_usuario = ?";
$stmt_update = $conexion->prepare($sql_update);

if (!$stmt_update) {
    responseJson(false, 'Error al preparar la actualización de la contraseña.');
}

$stmt_update->bind_param("si", $hashedPassword, $user['id_usuario']);

if ($stmt_update->execute()) {
    $stmt_update->close();
    $conexion->close();
    responseJson(true, '¡Contraseña actualizada con éxito!');
} else {
    $stmt_update->close();
    $conexion->close();
    responseJson(false, 'Error al guardar la nueva contraseña en la base de datos.');
}

?>