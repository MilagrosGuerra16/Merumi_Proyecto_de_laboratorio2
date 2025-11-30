<?php
include "conexion.php";

$nombre = $_POST['nombre'];
$email = $_POST['email'];
$celular = $_POST['celular'];
$pass = $_POST['contraseña'];

$hash = password_hash($pass, PASSWORD_DEFAULT);
$id_tipo = 2; // ✅ ADMINISTRADOR

$stmt = $conexion->prepare("
    INSERT INTO Usuario (nombre, email, celular, contraseña, id_Tipo_Persona)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("ssssi", $nombre, $email, $celular, $hash, $id_tipo);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error al crear admin"]);
}
