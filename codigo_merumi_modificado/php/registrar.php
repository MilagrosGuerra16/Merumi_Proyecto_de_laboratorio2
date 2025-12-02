<?php
// Muestra errores de PHP (solo para desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluye la conexión a la base de datos
include "conexion.php";

// Bloquea accesos que no sean por método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido');
}

// Recibe los datos enviados desde el formulario
$nombre     = $_POST['nombre'] ?? '';
$email      = $_POST['email'] ?? '';
$celular    = $_POST['celular'] ?? '';
$contrasena = $_POST['contraseña'] ?? '';
$id_tipo    = 1; // Tipo de persona: administrador

// Verifica que los campos obligatorios estén completos
if ($nombre === '' || $email === '' || $contrasena === '') {
    exit("Faltan datos");
}

// Encripta la contraseña antes de guardarla
$passHash = password_hash($contrasena, PASSWORD_DEFAULT);

// Prepara la consulta para insertar el usuario
$sql = $conexion->prepare(
    "INSERT INTO usuario (nombre, email, celular, contrasena, id_Tipo_Persona)
     VALUES (?, ?, ?, ?, ?)"
);

// Vincula los valores a la consulta preparada
$sql->bind_param(
    "ssssi",
    $nombre,
    $email,
    $celular,
    $passHash,
    $id_tipo
);

// Ejecuta la inserción y redirige si fue exitosa
if ($sql->execute()) {
    header("Location: ../inicio/iniciarSesion.html");
    exit;
} else {
    // Muestra error si falla el registro
    echo "Error al registrar: " . $conexion->error;
}

// Cierra la consulta y la conexión
$sql->close();
$conexion->close();
