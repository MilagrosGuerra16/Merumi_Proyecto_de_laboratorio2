<?php
// iniciar sesion
session_start();

// eliminar datos de la sesion
session_unset();
session_destroy();

// respuesta exitosa
echo json_encode(["success" => true]);
exit;
?>
