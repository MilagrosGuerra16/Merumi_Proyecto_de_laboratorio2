<?php
// Si este archivo está en merumi/php/
// y conexion.php también está en merumi/php/, entonces:
include "conexion.php"; // Incluir la conexión

// Recibir datos del formulario
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$celular = $_POST['celular'];
$contraseña = $_POST['contraseña']; // Contraseña ingresada por el usuario
$id_tipo = 1; // CLIENTE

// CAMBIO CLAVE: Encriptar contraseña usando password_hash()
$passHash = password_hash($contraseña, PASSWORD_DEFAULT);

$sql = $conexion->prepare("INSERT INTO Usuario (nombre, email, celular, contraseña, id_Tipo_Persona) 
VALUES (?, ?, ?, ?, ?)");

// 4. BIND PARAM: (s) nombre, (s) email, (s) celular, (s) contraseña_hash, (i) id_Tipo_Persona
$sql->bind_param("ssssi", $nombre, $email, $celular, $passHash, $id_tipo);

if ($sql->execute()) {
echo "Usuario registrado correctamente. Serás redirigido para iniciar sesión.";
header("refresh:3; url=../inicio/iniciarSesion.html"); 
} else {
 echo "Error al registrar: " . $conexion->error;
}

$sql->close(); // Cierra la sentencia preparada
$conexion->close();
?>