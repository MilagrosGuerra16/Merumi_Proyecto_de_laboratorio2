<?php
// Inicia la sesión. Es necesario para almacenar el estado de inicio de sesión del usuario.
session_start();

// Incluye el archivo de conexión a la base de datos. Ambos están en la misma carpeta 'php/'.
require_once("conexion.php");

/**
 * Muestra un mensaje de alerta en el navegador y redirige al usuario
 * a la página de inicio de sesión.
 * @param string $msg El mensaje de error a mostrar.
 */
function showErrorAndRedirect($msg){
    echo "<script>alert('$msg'); window.location='../inicio/iniciarSesion.html';</script>";
    exit;
}

// 1. Verificación del método de solicitud
// Solo procesa el código si la solicitud fue enviada por el formulario (método POST).
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../inicio/iniciarSesion.html");
    exit;
}

// 2. Capturar y limpiar datos del formulario
// trim() elimina espacios en blanco al inicio y final.
$email = trim($_POST["email"]);
$pass_ingresada = $_POST["contraseña"]; // Contraseña en texto plano ingresada por el usuario.

if (empty($email) || empty($pass_ingresada)) {
    showErrorAndRedirect("Completa todos los campos.");
}

// 3. Consulta a la base de datos (usando sentencia preparada por seguridad)
// Se busca al usuario por email para obtener su hash de contraseña y tipo de persona.
$sql = $conexion->prepare("SELECT id_usuario, contraseña, id_Tipo_Persona FROM Usuario WHERE email = ?");
$sql->bind_param("s", $email); // 's' indica que el parámetro es una cadena (string)
$sql->execute();
$result = $sql->get_result();

// 4. Verificar si el usuario existe
if ($result->num_rows !== 1) {
    showErrorAndRedirect("Email o contraseña incorrecta.");
}

$user = $result->fetch_assoc();
// $user["contraseña"] contiene el HASH (el código cifrado) guardado en la DB.

// 5. Verificación de la Contraseña (CLAVE DEL HASHING)
// password_verify() compara la contraseña en texto plano ($pass_ingresada) con el hash completo ($user["contraseña"]).
if (!password_verify($pass_ingresada, $user["contraseña"])) { 
    showErrorAndRedirect("Email o contraseña incorrecta.");
}

// 6. Inicio de sesión exitoso: Asignar variables de sesión
$_SESSION["loggedin"] = true; // Marca al usuario como logueado
$_SESSION["id_usuario"] = $user["id_usuario"];
$_SESSION["id_Tipo_Persona"] = $user["id_Tipo_Persona"];
$_SESSION["email"] = $email;

// 7. Redireccionar según el tipo de usuario (id_Tipo_Persona)
if ($user["id_Tipo_Persona"] == 1) {
    header("Location: ../menuClientes/menu.html");
    exit;
} else { 
    header("Location: ../menuAdm/menu.html");
    exit;
}

// Cerrar conexión para liberar recursos
// Usamos $conexion, no $conn
if (isset($conexion)) {
    $conexion->close();
}
?>