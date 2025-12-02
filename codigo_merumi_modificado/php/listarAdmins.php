<?php
// Respuesta en formato JSON
header("Content-Type: application/json");

// Conexión a la base de datos
require "conexion.php";

// Consulta: trae solo administradores (id_Tipo_Persona = 2)
$sql = "SELECT id_usuario, nombre, email, celular
        FROM usuario
        WHERE id_Tipo_Persona = 2
        ORDER BY id_usuario ASC";

// Ejecuta la consulta
$res = $conexion->query($sql);

// Array para guardar los administradores
$data = [];

// Guarda cada fila en el array
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

// Devuelve los datos en JSON
echo json_encode($data);
