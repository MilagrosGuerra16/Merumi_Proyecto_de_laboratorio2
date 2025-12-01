<?php
// Inicia la sesión
session_start();


require_once("conexion.php");


// funcion para mostrar error y volver al login
function showErrorAndRedirect($msg){
    echo "<script>alert('$msg'); window.location='../inicio/iniciarSesion.html';</script>";
    exit;
}


// bloquear acceso directo
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../inicio/iniciarSesion.html");
    exit;
}
// datos del formulario
$email = trim($_POST["email"]);
$pass_ingresada = $_POST["contrasena"]; 
// validacion
if (empty($email) || empty($pass_ingresada)) {
    showErrorAndRedirect("Completa todos los campos.");
}

// buscar usuario por email
$sql = $conexion->prepare("SELECT id_usuario, contrasena, id_Tipo_Persona FROM usuario WHERE email = ?");
$sql->bind_param("s", $email); 
$result = $sql->get_result();

// verificar existencia del usuario
if ($result->num_rows !== 1) {
    showErrorAndRedirect("Email o contrasena incorrecta.");
}

$user = $result->fetch_assoc();

// verificar contrasena
if (!password_verify($pass_ingresada, $user["contrasena"])) { 
    showErrorAndRedirect("Email o contrasena incorrecta.");
}

// iniciar sesion
$_SESSION["loggedin"] = true; 
$_SESSION["id_usuario"] = $user["id_usuario"];
$_SESSION["id_Tipo_Persona"] = $user["id_Tipo_Persona"];
$_SESSION["email"] = $email;

// redireccion segun tipo de usuario
if ($user["id_Tipo_Persona"] == 1) {
    header("Location: ../menuClientes/menu.html");
    exit;
} else { 
    header("Location: ../menuAdm/menu.html");
    exit;
}

// cerrar conexion
if (isset($conexion)) {
    $conexion->close();
}
?>