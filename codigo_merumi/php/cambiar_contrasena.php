<?php
// responder en formato JSON
header("Content-Type: application/json");

// conexion a la base de datos
require "conexion.php";

// funcion estandar de respuesta
function responseJson(bool $success, string $message) {
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);
    exit;
}

// validar metodo POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responseJson(false, "Método no permitido");
}

// obtener datos
$email       = $_POST["email"] ?? "";
$code        = $_POST["code"] ?? "";
$newPassword = $_POST["newPassword"] ?? "";

// validar campos
if (empty($email) || empty($code) || empty($newPassword)) {
    responseJson(false, "Faltan campos obligatorios");
}

// buscar usuario por email
$sql = "
    SELECT id_usuario, codigo_recuperacion, expiracion_codigo
    FROM usuario
    WHERE email = ?
";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    responseJson(false, "Error al preparar la consulta");
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    responseJson(false, "Correo electrónico no registrado");
}

$usuario = $result->fetch_assoc();
$stmt->close();

// validar codigo
if ($usuario["codigo_recuperacion"] !== $code) {
    responseJson(false, "Código de verificación incorrecto");
}

// validar expiracion
$ahora = new DateTime();
$expiracion = new DateTime($usuario["expiracion_codigo"]);

if ($ahora > $expiracion) {
    responseJson(false, "El código ha expirado");
}

// encriptar nueva contraseña
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

// actualizar contraseña y limpiar codigo
$sqlUpdate = "
    UPDATE usuario
    SET contrasena = ?, codigo_recuperacion = NULL, expiracion_codigo = NULL
    WHERE id_usuario = ?
";
$stmt = $conexion->prepare($sqlUpdate);

if (!$stmt) {
    responseJson(false, "Error al preparar la actualización");
}

$stmt->bind_param("si", $passwordHash, $usuario["id_usuario"]);

if ($stmt->execute()) {
    responseJson(true, "Contraseña actualizada con éxito");
} else {
    responseJson(false, "Error al guardar la contraseña");
}
