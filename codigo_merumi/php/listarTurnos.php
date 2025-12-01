<?php
// devolver respuesta en formato JSON
header('Content-Type: application/json; charset=utf-8');
require "conexion.php";
// consulta de turnos
$sql = "SELECT id_turnos, nombre, inicio_hora, fin_hora FROM turnos ORDER BY id_turnos";
$res = $conexion->query($sql);

$turnos = [];
while ($row = $res->fetch_assoc()) {// recorrer resultados
    $turnos[] = [
        "id_turno" => $row["id_turnos"],
        "nombre" => $row["nombre"],
        "inicio_hora" => $row["inicio_hora"],
        "fin_hora" => $row["fin_hora"]
    ];
}
// enviar datos en JSON
echo json_encode($turnos);
