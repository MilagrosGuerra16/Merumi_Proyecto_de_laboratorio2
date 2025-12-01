<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Verifica que sea una solicitud POST
    exit('Acceso no permitido');
}

//datos formulario
$nombre     = $_POST['nombre'] ?? '';
$email      = $_POST['email'] ?? '';
$celular    = $_POST['celular'] ?? '';
$contrasena = $_POST['contraseña'] ?? '';
$id_tipo    = 2;

// Validación 
if ($nombre === '' || $email === '' || $contrasena === '') {
    exit("Faltan datos");
}

//  Encriptar contraseña
$passHash = password_hash($contrasena, PASSWORD_DEFAULT);


$sql = $conexion->prepare( //insertar datos en la tabla usuario
    "INSERT INTO usuario (nombre, email, celular, contrasena, id_Tipo_Persona)
     VALUES (?, ?, ?, ?, ?)"
);

$sql->bind_param( // vincular parametros
    "ssssi",
    $nombre,
    $email,
    $celular,
    $passHash,
    $id_tipo
);

if ($sql->execute()) { //volver a inicio
    header("Location: ../inicio/iniciarSesion.html");
    exit;
} else {//mensaje de error
    echo "Error al registrar: " . $conexion->error;
}

$sql->close();
$conexion->close();
