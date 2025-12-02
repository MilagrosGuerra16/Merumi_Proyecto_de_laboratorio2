<?php
// Devuelve la respuesta en formato JSON con codificación UTF-8
header('Content-Type: application/json; charset=utf-8');

// Conexión a la base de datos
require "conexion.php";

// Consulta para obtener todos los turnos ordenados por ID
$sql = "SELECT id_turnos, nombre, inicio_hora, fin_hora FROM turnos ORDER BY id_turnos";
$res = $conexion->query($sql);

// Array donde se guardarán los turnos
$turnos = [];

// Recorre cada turno obtenido
while ($row = $res->fetch_assoc()) {
    $turnos[] = [
        "id_turno"     => $row["id_turnos"],   // ID del turno
        "nombre"       => $row["nombre"],      // Nombre del turno
        "inicio_hora"  => $row["inicio_hora"], // Hora de inicio
        "fin_hora"     => $row["fin_hora"]      // Hora de fin
    ];
}

// Envía el listado de turnos en formato JSON
echo json_encode($turnos);
?>
