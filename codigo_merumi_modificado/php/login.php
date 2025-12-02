<?php
// Inicia la sesión
session_start();

// Conexión a la base de datos
require_once("conexion.php");

// Muestra alerta y vuelve al login
function showErrorAndRedirect($msg){
    echo "<script>alert('$msg'); window.location='../inicio/iniciarSesion.html';</script>";
    exit;
}

// Solo permite acceso por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../inicio/iniciarSesion.html");
    exit;
}

// Toma datos del formulario
$email = trim($_POST["email"]);
$pass_ingresada = $_POST["contrasena"];

// Verifica campos obligatorios
if (empty($email) || empty($pass_ingresada)) {
    showErrorAndRedirect("Completa todos los campos.");
}

// Busca el usuario por email
$sql = $conexion->prepare(
    "SELECT id_usuario, contrasena, id_Tipo_Persona FROM usuario WHERE email = ?"
);
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();

// Verifica si existe el usuario
if ($result->num_rows !== 1) {
    showErrorAndRedirect("Email o contrasena incorrecta.");
}

$user = $result->fetch_assoc();

// Verifica la contraseña con hash
if (!password_verify($pass_ingresada, $user["contrasena"])) {
    showErrorAndRedirect("Email o contrasena incorrecta.");
}

// Guarda datos en la sesión
$_SESSION["loggedin"] = true;
$_SESSION["id_usuario"] = $user["id_usuario"];
$_SESSION["id_Tipo_Persona"] = $user["id_Tipo_Persona"];
$_SESSION["email"] = $email;

// Redirige según tipo de usuario
if ($user["id_Tipo_Persona"] == 1) {
    header("Location: ../menuClientes/menu.html");
} else {
    header("Location: ../menuAdm/menu.html");
}

// Cierra conexión
$conexion->close();
?>
