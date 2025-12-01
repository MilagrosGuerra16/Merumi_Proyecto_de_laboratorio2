<?php
// devolver respuesta en JSON
header("Content-Type: application/json");
require "conexion.php";
// consultar administradores
$sql = "SELECT id_usuario, nombre, email, celular
        FROM usuario
        WHERE id_Tipo_Persona = 2
        ORDER BY id_usuario ASC";

$res = $conexion->query($sql);
// guardar resultados
$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
