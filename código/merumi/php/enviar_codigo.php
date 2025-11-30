<?php
header('Content-Type: application/json');
require_once 'conexion.php';

// Función para respuesta JSON
function responseJson($success, $message, $codigo = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'codigo' => $codigo]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJson(false, 'Método no permitido.');
}

$email = $_POST['email'] ?? '';

if (empty($email)) {
    responseJson(false, 'Debes ingresar un correo electrónico.');
}

// Buscar usuario
$sql = "SELECT id_usuario FROM usuario WHERE email = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    responseJson(false, 'Correo no registrado.');
}

$user = $result->fetch_assoc();
$stmt->close();

// Generar código y guardar en DB (simulación o real)
$codigo = rand(100000, 999999);
$expiracion = date("Y-m-d H:i:s", strtotime("+10 minutes"));

$sql_update = "UPDATE usuario SET codigo_recuperacion = ?, expiracion_codigo = ? WHERE id_usuario = ?";
$stmt_update = $conexion->prepare($sql_update);
$stmt_update->bind_param("ssi", $codigo, $expiracion, $user['id_usuario']);
$stmt_update->execute();
$stmt_update->close();
$conexion->close();

// Responder con el código
responseJson(true, 'Código enviado correctamente.', $codigo);
?>
