<?php
// Responde siempre en formato JSON
header('Content-Type: application/json');

require_once 'conexion.php'; 

// Función de respuesta JSON
function responseJson(bool $success, string $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Solo permite peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJson(false, 'Método no permitido.');
}

// Recibe los datos del formulario
$email = $_POST['email'] ?? '';
$code = $_POST['code'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

// Validaciones básicas
if (empty($email) || empty($code) || empty($newPassword)) {
    responseJson(false, 'Faltan campos obligatorios.');
}

// Busca el usuario por email
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

// Obtiene los datos del usuario
$user = $result->fetch_assoc();
$stmt->close();

$current_time = new DateTime();
$expiration_time = new DateTime($user['expiracion_codigo']);

// Valida el código ingresado
if ($user['codigo_recuperacion'] !== $code) {
    responseJson(false, 'Código de verificación incorrecto.');
}
// Valida que el código no esté vencido
if ($current_time > $expiration_time) {
    responseJson(false, 'El código ha expirado. Vuelve a solicitar la recuperación.');
}

// Encripta la nueva contraseña
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Actualiza la contraseña y borra el código usado
$sql_update = "UPDATE usuario SET contrasena = ?, codigo_recuperacion = NULL, expiracion_codigo = NULL WHERE id_usuario = ?";
$stmt_update = $conexion->prepare($sql_update);

if (!$stmt_update) {
    responseJson(false, 'Error al preparar la actualización de la contraseña.');
}

$stmt_update->bind_param("si", $hashedPassword, $user['id_usuario']);
// Ejecuta la actualización
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