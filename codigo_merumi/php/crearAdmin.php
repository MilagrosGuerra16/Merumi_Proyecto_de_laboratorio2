<?php
// conexion a la base de datos
include "conexion.php";

// datos del formulario
$nombre   = $_POST['nombre']     ?? '';
$email    = $_POST['email']      ?? '';
$celular  = $_POST['celular']    ?? '';
$pass     = $_POST['contraseña'] ?? '';

// encriptar contraseña
$hash = password_hash($pass, PASSWORD_DEFAULT);

// tipo de usuario: administrador
$id_tipo = 2;

// insertar nuevo administrador
$stmt = $conexion->prepare("
    INSERT INTO usuario (nombre, email, celular, contrasena, id_Tipo_Persona)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssi",
    $nombre, $email, $celular, $hash, $id_tipo
);

// ejecutar insercion
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al crear admin"
    ]);
}

// cerrar consulta
$stmt->close();
